export class AppError extends Error {
  constructor(message, statusCode, isOperational = true) {
    super(message);
    this.statusCode = statusCode;
    this.isOperational = isOperational;
    Error.captureStackTrace(this, this.constructor);
  }
}

export class ValidationError extends AppError {
  constructor(message) {
    super(message, 400);
  }
}

export class DNSError extends AppError {
  constructor(message, isOperational = true) {
    super(message, 500, isOperational);
  }
}

export class LanguageError extends AppError {
  constructor(message) {
    super(message, 400);
  }
}

export class ConfigurationError extends AppError {
  constructor(message) {
    super(message, 500, false);
  }
}

export const handleError = (error) => {
  if (error instanceof AppError) {
    return {
      statusCode: error.statusCode,
      message: error.message,
      error: error.constructor.name,
    };
  }

  // Unexpected errors
  return {
    statusCode: 500,
    message: 'An unexpected error occurred',
    error: 'InternalServerError',
  };
};

export const assertDomain = (domain) => {
  if (!domain) {
    throw new ValidationError('Domain name is required');
  }

  const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
  if (!domainRegex.test(domain)) {
    throw new ValidationError('Invalid domain name format');
  }

  if (domain.length > 255) {
    throw new ValidationError('Domain name is too long');
  }
};

export const assertDNSServer = (serverIp) => {
  const ipv4Regex = /^(\d{1,3}\.){3}\d{1,3}$/;
  const ipv6Regex = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/;

  if (!ipv4Regex.test(serverIp) && !ipv6Regex.test(serverIp)) {
    throw new ValidationError('Invalid DNS server IP address');
  }
};

export default {
  AppError,
  ValidationError,
  DNSError,
  LanguageError,
  ConfigurationError,
  handleError,
  assertDomain,
  assertDNSServer,
};