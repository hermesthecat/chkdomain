import { promisify } from 'util';
import { exec } from 'child_process';
import winston from 'winston';

const execAsync = promisify(exec);

export class DomainChecker {
  constructor() {
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
   * Check if the platform is Windows
   */
  isWindows() {
    return process.platform === 'win32';
  }

  /**
   * Execute a DNS query using platform-specific commands
   */
  async executeQuery(domain, server, options = {}) {
    const startTime = Date.now();
    const timeout = options.timeout || 5000; // 5 seconds default timeout

    try {
      let command;
      if (this.isWindows()) {
        command = `nslookup ${domain} ${server}`;
      } else {
        const type = options.type || 'A';
        command = `dig @${server} ${domain} ${type} +short +timeout=${Math.ceil(timeout / 1000)}`;
      }

      const { stdout, stderr } = await execAsync(command, { timeout });
      if (stderr) {
        this.logger.warn('DNS query warning:', { domain, server, stderr });
      }

      const duration = Date.now() - startTime;
      this.logger.info('DNS query completed', {
        domain,
        server,
        duration,
        command,
      });

      return stdout;
    } catch (error) {
      const duration = Date.now() - startTime;
      this.logger.error('DNS query failed', {
        domain,
        server,
        duration,
        error: error.message,
      });
      throw error;
    }
  }

  /**
   * Parse the raw DNS query output
   */
  parseQueryOutput(output) {
    return output
      .split('\n')
      .map(line => line.trim())
      .filter(line => {
        // Filter out empty lines and common nslookup/dig headers
        return line &&
          !line.startsWith('Server:') &&
          !line.startsWith('Address:') &&
          !line.startsWith(';');
      });
  }

  /**
   * Query a domain using a specific DNS server
   */
  async query(domain, dnsServer, options = {}) {
    const startTime = Date.now();

    try {
      const rawOutput = await this.executeQuery(domain, dnsServer.ip, options);
      const parsedOutput = this.parseQueryOutput(rawOutput);

      const result = {
        status: 'resolved',
        ip: parsedOutput,
        raw: rawOutput,
        timestamp: new Date().toISOString(),
        serverInfo: {
          name: dnsServer.name,
          ip: dnsServer.ip,
          category: dnsServer.category,
        },
        responseTime: Date.now() - startTime,
      };

      if (parsedOutput.length === 0) {
        result.status = 'error';
        result.error = 'No records found';
      }

      return result;
    } catch (error) {
      return {
        status: 'error',
        error: error.message,
        timestamp: new Date().toISOString(),
        serverInfo: {
          name: dnsServer.name,
          ip: dnsServer.ip,
          category: dnsServer.category,
        },
        responseTime: Date.now() - startTime,
      };
    }
  }

  /**
   * Query a domain against multiple DNS servers
   */
  async queryAll(domain, dnsServers, options = {}) {
    const queries = dnsServers.map(server => this.query(domain, server, options));
    return Promise.all(queries);
  }
}

export default DomainChecker;