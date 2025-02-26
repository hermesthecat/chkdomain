import express from 'express';
import rateLimit from 'express-rate-limit';
import { DomainChecker } from '../lib/DomainChecker.js';
import { Language } from '../lib/Language.js';
import { dnsServers, getServersByCategory } from '../config/dns-servers.js';
import { assertDomain, ValidationError } from '../utils/errors.js';

const router = express.Router();
const domainChecker = new DomainChecker();
const language = new Language();

// Rate limiting middleware
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // Limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP, please try again later',
});

router.use(limiter);

// Get available DNS servers
router.get('/servers', (_req, res) => {
  const servers = {
    nofilter: getServersByCategory('nofilter'),
    secure: getServersByCategory('secure'),
    adblock: getServersByCategory('adblock'),
  };
  res.json(servers);
});

// Get available languages
router.get('/languages', (_req, res) => {
  res.json({
    current: language.getCurrentLanguage(),
    supported: language.getSupportedLanguages(),
  });
});

// Change language
router.post('/language', (req, res) => {
  const { lang } = req.body;
  
  if (!lang || typeof lang !== 'string') {
    throw new ValidationError('Language code is required');
  }

  language.changeLanguage(lang);
  res.json({ success: true, language: lang });
});

// Check domain
router.post('/check', async (req, res) => {
  const { domain, category, options } = req.body;
  
  // Validate domain
  assertDomain(domain);

  // Validate category
  if (category && !['nofilter', 'secure', 'adblock'].includes(category)) {
    throw new ValidationError('Invalid DNS server category');
  }

  // Validate options
  const queryOptions = {};
  if (options) {
    if (options.timeout && typeof options.timeout === 'number') {
      queryOptions.timeout = options.timeout;
    }
    if (options.type && typeof options.type === 'string') {
      queryOptions.type = options.type.toUpperCase();
    }
  }

  // Get DNS servers to query
  const servers = category ? getServersByCategory(category) : Object.values(dnsServers).flat();

  // Perform DNS queries
  const results = await domainChecker.queryAll(domain, servers, queryOptions);

  res.json({
    domain,
    timestamp: new Date().toISOString(),
    results,
  });
});

export default router;