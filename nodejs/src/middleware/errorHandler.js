import winston from 'winston';
import { handleError } from '../utils/errors.js';

// Create logger instance
const logger = winston.createLogger({
  level: 'error',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.json()
  ),
  transports: [
    new winston.transports.Console({
      format: winston.format.simple(),
    }),
  ],
});

export const errorHandler = (err, req, res, _next) => {
  const errorResponse = handleError(err);

  // Log error details
  logger.error('Error occurred', {
    path: req.path,
    method: req.method,
    error: err.message,
    stack: err.stack,
    statusCode: errorResponse.statusCode,
  });

  // Send error response
  res.status(errorResponse.statusCode).json({
    success: false,
    error: errorResponse.error,
    message: errorResponse.message,
    timestamp: new Date().toISOString(),
  });
};

// Handle 404 errors
export const notFoundHandler = (req, res) => {
  res.status(404).json({
    success: false,
    error: 'NotFound',
    message: `Route ${req.method} ${req.path} not found`,
    timestamp: new Date().toISOString(),
  });
};

// Handle uncaught exceptions and unhandled rejections
export const setupErrorLogging = () => {
  process.on('uncaughtException', (error) => {
    logger.error('Uncaught Exception', {
      error: error.message,
      stack: error.stack,
    });
    process.exit(1);
  });

  process.on('unhandledRejection', (reason) => {
    logger.error('Unhandled Rejection', {
      reason: reason instanceof Error ? reason.message : String(reason),
      stack: reason instanceof Error ? reason.stack : undefined,
    });
    process.exit(1);
  });
};

export default {
  errorHandler,
  notFoundHandler,
  setupErrorLogging,
};