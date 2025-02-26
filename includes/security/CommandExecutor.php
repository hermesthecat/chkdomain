<?php

/**
 * CommandExecutor Class
 *
 * Provides secure command execution for DNS queries.
 */
class CommandExecutor {
    /**
     * Escapes shell arguments to prevent command injection.
     *
     * @param string $arg The argument to escape.
     * @return string The escaped argument.
     */
    private static function escapeArgument($arg) {
        // Remove any shell metacharacters
        $escaped = preg_replace('/[&;`$<>|]/', '', $arg);
        // Escape quotes and spaces
        return escapeshellarg($escaped);
    }

    /**
     * Executes a DNS query with the specified parameters.
     *
     * @param string $queryType The type of DNS query (dig or nslookup).
     * @param string $domain The domain to query.
     * @param string $dnsServer The DNS server to use.
     * @return array The output of the command.
     * @throws SecurityException If the input is invalid.
     * @throws QueryException If the command execution fails.
     */
    public static function executeDnsQuery($queryType, $domain, $dnsServer) {
        // Validate inputs
        if (!self::isValidDomain($domain)) {
            throw new SecurityException('Invalid domain format');
        }
        if (!self::isValidDnsServer($dnsServer)) {
            throw new SecurityException('Invalid DNS server');
        }

        // Build command based on type
        switch ($queryType) {
            case 'dig':
                $command = sprintf('dig +short %s @%s',
                    self::escapeArgument($domain),
                    self::escapeArgument($dnsServer)
                );
                break;
            case 'nslookup':
                $command = sprintf('nslookup %s %s',
                    self::escapeArgument($domain),
                    self::escapeArgument($dnsServer)
                );
                break;
            default:
                throw new SecurityException('Invalid query type');
        }

        // Execute with proper error handling
        return self::executeCommand($command);
    }

    /**
     * Executes a command with error capturing.
     *
     * @param string $command The command to execute.
     * @return array The output of the command.
     * @throws QueryException If the command execution fails.
     */
    private static function executeCommand($command) {
        // Set execution time limit
        $timeout = 10;
        
        // Execute with error capturing
        $output = [];
        $returnVar = -1;
        
        exec($command . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new QueryException('Command execution failed: ' . implode("\n", $output));
        }
        
        return $output;
    }

    /**
     * Validates a domain name.
     *
     * @param string $domain The domain name to validate.
     * @return bool True if the domain name is valid, false otherwise.
     */
    private static function isValidDomain($domain) {
        return (
            // RFC 1034 compliance
            preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain) &&
            // Length check
            strlen($domain) <= 253
        );
    }

    /**
     * Validates a DNS server.
     *
     * @param string $server The DNS server to validate.
     * @return bool True if the DNS server is valid, false otherwise.
     */
    private static function isValidDnsServer($server) {
        return (
            // IPv4
            filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
            // IPv6
            filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
            // Hostname
            self::isValidDomain($server)
        );
    }
}

/**
 * SecurityException Class
 *
 * Represents a security-related exception.
 */
class SecurityException extends Exception {
}

/**
 * QueryException Class
 *
 * Represents a query-related exception.
 */
class QueryException extends Exception {
}