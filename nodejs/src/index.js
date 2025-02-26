import express from 'express';
import cors from 'cors';
import winston from 'winston';
import swaggerUi from 'swagger-ui-express';
import { readFileSync } from 'fs';
import { parse } from 'yaml';
import { join } from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import apiRouter from './routes/api.js';
import { Language } from './lib/Language.js';
import { errorHandler, notFoundHandler, setupErrorLogging } from './middleware/errorHandler.js';

// Get directory name for ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Initialize logger
const logger = winston.createLogger({
  level: 'info',
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

// Initialize language system
const language = new Language();

// Load Swagger documentation
const swaggerDocument = parse(readFileSync(join(__dirname, 'docs/swagger.yaml'), 'utf8'));

// Create Express application
const app = express();

// Basic middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Swagger UI
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerDocument));

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    timestamp: new Date().toISOString(),
    language: language.getCurrentLanguage()
  });
});

// API routes
app.use('/api', apiRouter);

// Error handling
app.use(notFoundHandler);
app.use(errorHandler);

// Set up error logging
setupErrorLogging();

const PORT = process.env.PORT || 3000;

// Initialize language system and start server
const startServer = async () => {
  try {
    await language.initialize();
    logger.info('Language system initialized');

    app.listen(PORT, () => {
      logger.info(`Server running on port ${PORT}`);
      logger.info(`API documentation available at http://localhost:${PORT}/api-docs`);
      logger.info(`Health check available at http://localhost:${PORT}/health`);
      logger.info(`API endpoints available at http://localhost:${PORT}/api/*`);
    });
  } catch (error) {
    logger.error('Failed to start server:', error);
    process.exit(1);
  }
};

startServer().catch(error => {
  logger.error('Unhandled server startup error:', error);
  process.exit(1);
});

export default app;