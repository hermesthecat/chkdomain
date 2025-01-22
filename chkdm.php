<?php
// CLI modunda çalışıyorsa shebang satırını ekle
if (php_sapi_name() === 'cli') {
    echo "#!/usr/bin/env php\n";
}

/**
 * chkdm (chkdomain)
 * PHP version of https://github.com/PeterDaveHello/chkdomain
 * @author A. Kerem Gök
 * @license GPL-3.0
 */

// CLI modunda çalışıyorsa renk çıktıları aktif olsun
if (php_sapi_name() === 'cli') {
    class ColorEcho
    {
        private static function colorize($text, $color)
        {
            $colors = [
                'red' => "\033[31m",
                'green' => "\033[32m",
                'cyan' => "\033[36m",
                'boldblack' => "\033[1;30m"
            ];
            return $colors[$color] . $text . "\033[m";
        }

        public static function red($text)
        {
            echo self::colorize($text, 'red') . PHP_EOL;
        }

        public static function green($text)
        {
            echo self::colorize($text, 'green') . PHP_EOL;
        }

        public static function cyan($text)
        {
            echo self::colorize($text, 'cyan') . PHP_EOL;
        }

        public static function boldBlack($text)
        {
            echo self::colorize($text, 'boldblack') . PHP_EOL;
        }
    }
} else {
    class ColorEcho
    {
        public static function red($text)
        {
            echo $text . "\n";
        }
        public static function green($text)
        {
            echo $text . "\n";
        }
        public static function cyan($text)
        {
            echo $text . "\n";
        }
        public static function boldBlack($text)
        {
            echo $text . "\n";
        }
    }
}

function error($message)
{
    ColorEcho::red($message);
    exit(1);
}

// İşletim sistemi kontrolü
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// Gerekli komutların kontrolü
if ($isWindows) {
    $requiredCommands = ['nslookup'];
} else {
    $requiredCommands = ['dig', 'nslookup', 'sed', 'head', 'awk', 'sort', 'dirname', 'readlink'];
}

foreach ($requiredCommands as $cmd) {
    if ($isWindows) {
        exec("where $cmd 2>&1", $output, $returnVar);
    } else {
        exec("command -v $cmd 2>&1", $output, $returnVar);
    }
    if ($returnVar !== 0) {
        error("command: $cmd not found!");
    }
}

// DNS sunucuları tanımlamaları
$nofilterDNS = [
    'AdGuard' => '94.140.14.140',
    'Cloudflare' => '1.1.1.1',
    'dns0.eu' => '193.110.81.254',
    'Gcore' => '95.85.95.85',
    'Google' => '8.8.8.8',
    'Hinet' => '168.95.1.1',
    'UltraDNS' => '64.6.64.6',
    'OpenDNS' => '208.67.222.2',
    'Quad9' => '9.9.9.10',
    'Yandex' => '77.88.8.1'
];

$secureDNS = [
    'CleanBrowsing' => '185.228.168.9',
    'Cloudflare' => '1.1.1.2',
    'Comodo' => '8.26.56.26',
    'CONTROL D' => '76.76.2.1',
    'dns0.eu' => '193.110.81.0',
    'UltraDNS' => '156.154.70.2',
    'SafeDNS' => '195.46.39.39',
    'OpenDNS' => '208.67.222.222',
    'Quad101' => '101.101.101.101',
    'Quad9' => '9.9.9.9',
    'Yandex' => '77.88.8.2'
];

$adblockDNS = [
    'AdGuard' => '94.140.14.14',
    'CONTROL D' => '76.76.2.2',
    'dnsforge.de' => '176.9.93.198',
    'OVPN' => '192.165.9.157',
    'Tiarap' => '188.166.206.224'
];

$PaloAltoSinkholeCname = "sinkhole.paloaltonetworks.com.";
$NextDNSBlockPageCname = "blockpage.nextdns.io.";
$NextDNSBlockPageIP = trim(shell_exec("dig +short $NextDNSBlockPageCname"));

function query($domain, $dns, $filterDetect = false)
{
    global $PaloAltoSinkholeCname, $NextDNSBlockPageCname, $NextDNSBlockPageIP, $isWindows;

    if ($isWindows) {
        $result = trim(shell_exec("nslookup -type=A $domain $dns 2>&1"));
        // Windows'ta nslookup çıktısını işle
        if (strpos($result, "Server failed") !== false || strpos($result, "No response from server") !== false) {
            return [4, $result];
        }

        preg_match_all('/Address(?:\(es\))?:\s*([^\s]+)/', $result, $matches);
        if (!empty($matches[1])) {
            $result = end($matches[1]); // Son IP adresini al
        } else {
            $result = "";
        }
    } else {
        $result = trim(shell_exec("dig +short $domain @$dns"));
    }

    if (strpos($result, $PaloAltoSinkholeCname) !== false) {
        return [2, $result];
    }

    if (!empty($NextDNSBlockPageIP) && (strpos($result, $NextDNSBlockPageCname) !== false || $NextDNSBlockPageIP === $result)) {
        return [3, $result];
    }

    if ($isWindows) {
        if (strpos($result, "timed out") !== false || strpos($result, "No response from server") !== false) {
            return [4, $result];
        }
        if (strpos($result, "refused") !== false) {
            return [5, $result];
        }
    } else {
        if (strpos($result, "connection timed out; no servers could be reached") !== false) {
            return [4, $result];
        }
        if (preg_match("/communications error to $dns#[1-9]+: timed out/", $result)) {
            return [4, $result];
        }
        if (preg_match("/communications error to $dns#[0-9]+: connection refused/", $result)) {
            return [5, $result];
        }
    }

    if ($filterDetect !== "filterDetect") {
        return [empty($result) ? 1 : 0, $result];
    }

    $resultHead = explode(" ", $result)[0];

    switch ($resultHead) {
        case "":
        case "127.0.0.1":
        case "0.0.0.0":
        case "::":
        case "127.0.0.2":
        case "195.46.39.1":
        case "156.154.112.16":
        case "156.154.113.16":
        case "52.15.96.207":
        case "146.112.61.108":
        case "safe1.yandex.ru.":
        case "213.180.193.250":
        case "93.158.134.250":
        case "2a02:6b8::b10c:bad":
        case "2a02:6b8::b10c:babe":
            return [1, $result];
        default:
            return [0, $result];
    }
}

function detailQuery($domain, $dns)
{
    global $isWindows;
    $result = shell_exec("nslookup $domain $dns");
    $lines = explode("\n", $result);
    $filtered = [];

    foreach ($lines as $line) {
        if ($isWindows) {
            if (!preg_match('/^(Server|Address|DNS request timed out|>|Name)/', trim($line)) && trim($line) !== '') {
                $filtered[] = "   " . trim($line);
            }
        } else {
            if (
                !preg_match('/^(Server|Address):\t.+/', $line) &&
                !preg_match('/.+answer:$/', $line) &&
                !preg_match('/^Name:\t+.+$/', $line) &&
                trim($line) !== ''
            ) {
                $filtered[] = "   " . trim($line);
            }
        }
    }

    sort($filtered);
    return implode("\n", array_unique($filtered));
}

function chkDomain($domain, $dns, $filterDetect = false)
{
    list($status, $queryResult) = query($domain, $dns, $filterDetect);

    switch ($status) {
        case 0:
            echo ColorEcho::green("OK!") . " " . ColorEcho::boldBlack("($queryResult)");
            break;
        case 1:
            ColorEcho::red("Failed!");
            echo detailQuery($domain, $dns);
            break;
        case 2:
            ColorEcho::red("Palo Alto DNS Sinkhole detected!");
            break;
        case 3:
            ColorEcho::red("NextDNS Block Page detected!");
            break;
        case 4:
            echo "Connection timed out ...";
            break;
        case 5:
            echo "Connection refused ...";
            break;
        default:
            error("Unknown error");
    }
}

function warnUpDNS($domain, $nofilterDNS, $secureDNS, $adblockDNS)
{
    global $isWindows;
    $allDNS = array_merge($nofilterDNS, $secureDNS, $adblockDNS);
    foreach ($allDNS as $dns) {
        if ($isWindows) {
            shell_exec("start /B nslookup $domain $dns >nul 2>&1");
        } else {
            shell_exec("dig +short $domain @$dns > /dev/null 2>&1 &");
        }
    }
}

function checkDNSGroup($groupName, $dnsServers, $domain, $filterDetect = false)
{
    ColorEcho::cyan("\nRunning dig/nslookup over " . count($dnsServers) . " $groupName:");
    ksort($dnsServers);
    foreach ($dnsServers as $name => $ip) {
        echo " - $name " . ColorEcho::boldBlack("($ip)") . " ... ";
        chkDomain($domain, $ip, $filterDetect);
    }
}

function checkDefaultDNS($domain)
{
    global $isWindows;
    if ($isWindows) {
        // Windows'ta varsayılan DNS sunucusunu al
        $cmd = 'ipconfig /all | findstr "DNS Servers" | findstr /v "::1" | findstr /v "127.0.0.1" | findstr /R "[0-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*"';
        $result = shell_exec($cmd);
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $result, $matches)) {
            $defaultDNS = $matches[0];
        } else {
            $defaultDNS = "8.8.8.8"; // Varsayılan olarak Google DNS
        }
    } else {
        $defaultDNS = trim(shell_exec("nslookup wikipedia.org | awk '/Server:/ {print $2}' | head -n 1"));
    }
    ColorEcho::cyan("\nRunning nslookup over default DNS ($defaultDNS):");
    echo " - $defaultDNS ... ";
    chkDomain($domain, $defaultDNS, "filterDetect");
}

function showDomainIntel($domain)
{
    ColorEcho::cyan("\nGet more intels about this domain from:");
    $urls = [
        "AlienVault Open Threat Exchange" => "https://otx.alienvault.com/indicator/domain/$domain",
        "Bitdefender TrafficLight" => "https://trafficlight.bitdefender.com/info/?url=https%3A%2F%2F$domain",
        "Google Safe Browsing" => "https://transparencyreport.google.com/safe-browsing/search?url=$domain",
        "Kaspersky Threat Intelligence Portal" => "https://opentip.kaspersky.com/$domain?tab=web",
        "McAfee SiteAdvisor" => "https://siteadvisor.com/sitereport.html?url=$domain",
        "Norton Safe Web" => "https://safeweb.norton.com/report/show?url=$domain",
        "OpenDNS" => "https://domain.opendns.com/$domain",
        "URLVoid" => "https://www.urlvoid.com/scan/$domain/",
        "urlscan.io" => "https://urlscan.io/domain/$domain",
        "VirusTotal" => "https://www.virustotal.com/gui/domain/$domain/detection",
        "Whois.com" => "https://www.whois.com/whois/$domain",
        "Yandex Site safety report" => "https://yandex.com/safety/?l10n=en&url=$domain"
    ];

    foreach ($urls as $name => $url) {
        echo "  - $name\n    $url\n";
    }
}

// CLI modunda çalışıyorsa ana programı çalıştır
if (php_sapi_name() === 'cli') {
    if ($argc !== 2) {
        error("You need to give me just one domain name to run the check!");
    }

    $domain = rtrim($argv[1], '.');

    // Domain validasyonu
    if (
        strlen($domain) > 253 ||
        !preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain) ||
        preg_match('/[a-zA-Z0-9-]{64,}/', $domain) ||
        strpos($domain, '--') !== false
    ) {
        error("Invalid domain name format! Please use format like 'example.com' or 'sub.example.com'");
    }

    ColorEcho::cyan("You are checking domain: $domain");

    // DNS kontrolleri
    warnUpDNS($domain, $nofilterDNS, $secureDNS, $adblockDNS);

    checkDNSGroup("nofilter DNS", $nofilterDNS, $domain, false);
    checkDNSGroup("secure DNS", $secureDNS, $domain, "filterDetect");
    checkDNSGroup("AD(and tracker)-blocking DNS", $adblockDNS, $domain, "filterDetect");
    checkDefaultDNS($domain);

    showDomainIntel($domain);
}
