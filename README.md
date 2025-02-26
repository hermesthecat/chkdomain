# Domain Checker (chkdomain)

[![Türkçe](https://img.shields.io/badge/Türkçe-TR-blue.svg)](https://github.com/yourusername/chkdomain/blob/main/README.md#türkçe)
[![English](https://img.shields.io/badge/English-US-green.svg)](https://github.com/yourusername/chkdomain/blob/main/README.md#english)

---

This project provides a PHP-based tool for checking domain names against various DNS servers and security blacklists. It offers both a web interface and a command-line interface (CLI) for flexible usage.

## Key Features

- **Comprehensive DNS Checks:** Verify domain accessibility and security across multiple DNS providers.
- **Categorized DNS Servers:** Test against normal, secure, and ad-blocking DNS server lists.
- **Custom DNS Configuration:** Add your own DNS servers for tailored testing.
- **Security Intelligence Integration:** Links to reputable security services for threat assessment.
- **Modern User Interface:** A responsive web interface built with AJAX for real-time results.
- **Command-Line Interface:** A powerful CLI tool for automated or script-based domain analysis.

## Requirements

- PHP 7.4 or higher
- Command-line tools: `dig`, `nslookup`, `sed`, `head`, `awk`, `sort`, `dirname`, `readlink` (some are OS-dependent)

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/chkdomain.git
   cd chkdomain
   ```

2. Set file permissions:

   ```bash
   chmod +x chkdm.php
   ```

## Usage

### Web Interface

1. Start PHP's built-in web server:

   ```bash
   php -S localhost:8000
   ```

2. Visit `http://localhost:8000` in your browser.
3. Enter a domain name and click "Check".
4. Review results from different DNS servers and security services.

### Command Line (CLI)

```bash
./chkdm.php example.com
# or
php chkdm.php example.com
```

## Custom DNS Servers

To add your own DNS servers:

1. Create a `CustomDNS.txt` file in the project directory.
2. Add one DNS server IP address per line.
3. Use `#` for comments.

Example `CustomDNS.txt`:

```
# Local ISP DNS
192.168.1.1
192.168.1.2

# Google DNS
8.8.8.8
8.8.4.4
```

## Supported DNS Servers

### Normal DNS Servers

- AdGuard (94.140.14.140)
- Cloudflare (1.1.1.1)
- Google (8.8.8.8)
- OpenDNS (208.67.222.2)
- Quad9 (9.9.9.10)
- and others...

### Secure DNS Servers

- CleanBrowsing (185.228.168.9)
- Cloudflare (1.1.1.2)
- Comodo (8.26.56.26)
- OpenDNS (208.67.222.222)
- Quad9 (9.9.9.9)
- and others...

### Ad-blocking DNS Servers

- AdGuard (94.140.14.14)
- CONTROL D (76.76.2.2)
- dnsforge.de (176.9.93.198)
- and others...

## Security Checks

Domains can be checked through the following services:

- AlienVault OTX
- Bitdefender TrafficLight
- Google Safe Browsing
- Kaspersky Threat Intelligence
- McAfee SiteAdvisor
- Norton Safe Web
- OpenDNS
- URLVoid
- urlscan.io
- VirusTotal
- Whois.com
- Yandex Site Safety

## License

This project is licensed under the GPL-3.0 license. See [LICENSE](LICENSE) for details.

## Author

A. Kerem Gök

## Contributors

- Peter Dave Hello (Original bash script)

---

# English

A PHP application that checks domain names through various DNS servers and displays security information.

## Features

- Domain checking through multiple DNS servers
- Support for normal, secure, and ad-blocking DNS servers
- Custom DNS servers support (CustomDNS.txt)
- Domain security information and scanning services
- Modern web interface and CLI support
- Real-time results with AJAX
- Responsive design

## Requirements

- PHP 7.4 or higher
- dig
- nslookup
- sed
- head
- awk
- sort
- dirname
- readlink

## Installation

1. Clone the repository:

```bash
git clone https://github.com/yourusername/chkdomain.git
cd chkdomain
```

2. Set file permissions:

```bash
chmod +x chkdm.php
```

## Usage

### Web Interface

1. Start PHP's built-in web server:

```bash
php -S localhost:8000
```

2. Visit `http://localhost:8000` in your browser
3. Enter a domain name and click "Check"
4. View results:
   - Normal DNS servers
   - Secure DNS servers
   - Ad-blocking DNS servers
   - Default DNS server
   - Custom DNS servers (if any)
   - Domain security information and links

### Command Line (CLI)

```bash
./chkdm.php example.com
# or
php chkdm.php example.com
```

## Custom DNS Servers

To add your own DNS servers:

1. Create a `CustomDNS.txt` file in the project directory
2. Add one DNS server IP address per line
3. Use # for comments

Example `CustomDNS.txt`:

```
# Local ISP DNS
192.168.1.1
192.168.1.2

# Google DNS
8.8.8.8
8.8.4.4
```

## Supported DNS Servers

### Normal DNS Servers

- AdGuard (94.140.14.140)
- Cloudflare (1.1.1.1)
- Google (8.8.8.8)
- OpenDNS (208.67.222.2)
- Quad9 (9.9.9.10)
- and others...

### Secure DNS Servers

- CleanBrowsing (185.228.168.9)
- Cloudflare (1.1.1.2)
- Comodo (8.26.56.26)
- OpenDNS (208.67.222.222)
- Quad9 (9.9.9.9)
- and others...

### Ad-blocking DNS Servers

- AdGuard (94.140.14.14)
- CONTROL D (76.76.2.2)
- dnsforge.de (176.9.93.198)
- and others...

## Security Checks

Domains can be checked through the following services:

- AlienVault OTX
- Bitdefender TrafficLight
- Google Safe Browsing
- Kaspersky Threat Intelligence
- McAfee SiteAdvisor
- Norton Safe Web
- Norton Safe Web
- OpenDNS
- URLVoid
- urlscan.io
- VirusTotal
- Whois.com
- Yandex Site Safety

## License

This project is licensed under GPL-3.0. See [LICENSE](LICENSE) file for details.

## Author

A. Kerem Gök

## Contributors

- Peter Dave Hello (Original bash script)
