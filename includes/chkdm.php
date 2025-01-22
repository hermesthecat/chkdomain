<?php

/**
 * chkdm (chkdomain)
 * PHP version of https://github.com/PeterDaveHello/chkdomain
 * @author A. Kerem Gök
 * @license GPL-3.0
 */

require_once __DIR__ . '/language.php';
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
$NextDNSBlockPageIP = trim(shell_exec("dig +short $NextDNSBlockPageCname"));

function query($domain, $dns, $filterDetect = false)
{
    global $PaloAltoSinkholeCname, $NextDNSBlockPageCname, $NextDNSBlockPageIP, $isWindows, $lang;

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
        return [2, $lang->get('status_sinkhole')];
    }

    if (!empty($NextDNSBlockPageIP) && (strpos($result, $NextDNSBlockPageCname) !== false || $NextDNSBlockPageIP === $result)) {
        return [3, $lang->get('status_blockpage')];
    }

    if ($isWindows) {
        if (strpos($result, "timed out") !== false || strpos($result, "No response from server") !== false) {
            return [4, $lang->get('status_timeout')];
        }
        if (strpos($result, "refused") !== false) {
            return [5, $lang->get('status_refused')];
        }
    } else {
        if (strpos($result, "connection timed out; no servers could be reached") !== false) {
            return [4, $lang->get('status_timeout')];
        }
        if (preg_match("/communications error to $dns#[1-9]+: timed out/", $result)) {
            return [4, $lang->get('status_timeout')];
        }
        if (preg_match("/communications error to $dns#[0-9]+: connection refused/", $result)) {
            return [5, $lang->get('status_refused')];
        }
    }

    if ($filterDetect !== "filterDetect") {
        if (empty($result)) {
            return [1, $lang->get('status_failed')];
        }
        return [0, $result . " (" . $lang->get('status_ok') . ")"];
    }

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
