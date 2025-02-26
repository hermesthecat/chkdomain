import i18next from 'i18next';
import winston from 'winston';

export class Language {
  constructor() {
    this.defaultLanguage = 'en';
    this.supportedLanguages = ['en', 'de', 'es', 'fr', 'it', 'ja', 'ru', 'tr', 'zh'];
    
    this.logger = winston.createLogger({
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
  }

  /**
   * Initialize i18next with configurations
   */
  async initialize() {
    try {
      await i18next.init({
        lng: this.defaultLanguage,
        fallbackLng: this.defaultLanguage,
        supportedLngs: this.supportedLanguages,
        ns: ['common', 'errors'],
        defaultNS: 'common',
        backend: {
          loadPath: 'locales/{{lng}}/{{ns}}.json',
        },
        interpolation: {
          escapeValue: false,
        },
      });

      this.logger.info('Language system initialized', {
        defaultLanguage: this.defaultLanguage,
        supportedLanguages: this.supportedLanguages,
      });
    } catch (error) {
      this.logger.error('Failed to initialize language system', {
        error: error instanceof Error ? error.message : String(error),
      });
      throw error;
    }
  }

  /**
   * Change the current language
   */
  async changeLanguage(lang) {
    try {
      if (!this.supportedLanguages.includes(lang)) {
        throw new Error(`Language ${lang} is not supported`);
      }

      await i18next.changeLanguage(lang);
      this.logger.info('Language changed', { language: lang });
    } catch (error) {
      this.logger.error('Failed to change language', {
        language: lang,
        error: error instanceof Error ? error.message : String(error),
      });
      throw error;
    }
  }

  /**
   * Get current language
   */
  getCurrentLanguage() {
    return i18next.language;
  }

  /**
   * Get list of supported languages
   */
  getSupportedLanguages() {
    return this.supportedLanguages;
  }

  /**
   * Translate a key
   */
  translate(key, options = {}) {
    const translation = i18next.t(key, options);
    return typeof translation === 'string' ? translation : JSON.stringify(translation);
  }
}

export default Language;