<?php

/**
 * chkdm (chkdomain)
 * PHP version of https://github.com/PeterDaveHello/chkdomain
 * @author A. Kerem Gök
 * @license GPL-3.0
 */

require_once __DIR__ . '/language.php';
require_once __DIR__ . '/security/CommandExecutor.php';

$lang = Language::getInstance();

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
        throw new Exception("command: $cmd " . $lang->get('error_command_not_found'));
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
try {
    $NextDNSBlockPageIP = trim(CommandExecutor::executeDnsQuery('dig', $NextDNSBlockPageCname, '8.8.8.8')[0]);
} catch (SecurityException $e) {
    $NextDNSBlockPageIP = "";
} catch (QueryException $e) {
    $NextDNSBlockPageIP = "";
}

function query($domain, $dns, $filterDetect = false)
{
    global $PaloAltoSinkholeCname, $NextDNSBlockPageCname, $NextDNSBlockPageIP, $lang;

    try {
        if ($filterDetect !== "filterDetect") {
            $result = CommandExecutor::executeDnsQuery('dig', $domain, $dns);
            $result = trim(implode("\n", $result));

            if (strpos($result, $PaloAltoSinkholeCname) !== false) {
                return [2, $lang->get('status_sinkhole')];
            }

            if (!empty($NextDNSBlockPageIP) && (strpos($result, $NextDNSBlockPageCname) !== false || $NextDNSBlockPageIP === $result)) {
                return [3, $lang->get('status_blockpage')];
            }

            if (empty($result)) {
                return [1, $lang->get('status_failed')];
            }

            return [0, $result . " (" . $lang->get('status_ok') . ")"];
        } else {
            $result = CommandExecutor::executeDnsQuery('dig', $domain, $dns);
            $result = trim(implode("\n", $result));

            $resultHead = explode(" ", $result)[0];

            // Filtre kontrolü
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
                    return [1, $lang->get('status_failed')];
                default:
                    return [0, $result];
            }
        }
    } catch (SecurityException $e) {
        return [6, $lang->get('error_security') . ": " . $e->getMessage()];
    } catch (QueryException $e) {
        return [4, $lang->get('status_timeout') . ": " . $e->getMessage()];
    }
}

function detailQuery($domain, $dns)
{
    global $isWindows, $lang;

    try {
        $result = CommandExecutor::executeDnsQuery('nslookup', $domain, $dns);
        $filtered = [];

        foreach ($result as $line) {
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
    } catch (SecurityException $e) {
        return $lang->get('error_security') . ": " . $e->getMessage();
    } catch (QueryException $e) {
        return $lang->get('status_timeout') . ": " . $e->getMessage();
    }
}
