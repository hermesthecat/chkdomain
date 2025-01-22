<?php

/**
 * Domain Check API
 * @author A. Kerem Gök
 */

header('Content-Type: application/json');

if (!isset($_POST['domain']) || !isset($_POST['type'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Domain ve sorgu tipi parametreleri gerekli']));
}

$validTypes = ['nofilterDNS', 'secureDNS', 'adblockDNS', 'defaultDNS', 'intelLinks', 'all'];
if (!in_array($_POST['type'], $validTypes)) {
    http_response_code(400);
    die(json_encode(['error' => 'Geçersiz sorgu tipi']));
}

require_once 'chkdm.php';

class DomainChecker
{
    private $domain;
    private $results = [];

    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->results = [
            'nofilterDNS' => [],
            'secureDNS' => [],
            'adblockDNS' => [],
            'defaultDNS' => [],
            'customDNS' => [],
            'intelLinks' => []
        ];
    }

    private function processQueryResult($name, $ip, $queryResult)
    {
        list($status, $result) = $queryResult;

        $response = [
            'ip' => $ip,
            'status' => 'ok',
            'message' => $result
        ];

        switch ($status) {
            case 0:
                $response['message'] = "OK! ($result)";
                break;
            case 1:
                $response['status'] = 'error';
                $response['message'] = 'Başarısız!';
                break;
            case 2:
                $response['status'] = 'error';
                $response['message'] = 'Palo Alto DNS Sinkhole tespit edildi!';
                break;
            case 3:
                $response['status'] = 'error';
                $response['message'] = 'NextDNS Block Page tespit edildi!';
                break;
            case 4:
                $response['status'] = 'warning';
                $response['message'] = 'Bağlantı zaman aşımına uğradı...';
                break;
            case 5:
                $response['status'] = 'warning';
                $response['message'] = 'Bağlantı reddedildi...';
                break;
        }

        return $response;
    }

    public function checkDNSGroup($groupName, $servers)
    {
        foreach ($servers as $name => $ip) {
            $queryResult = query($this->domain, $ip, $groupName !== 'nofilterDNS');
            $this->results[$groupName][$name] = $this->processQueryResult($name, $ip, $queryResult);
        }
    }

    public function checkDefaultDNS()
    {
        $defaultDNS = trim(shell_exec("nslookup wikipedia.org | awk '/Server:/ {print $2}' | head -n 1"));
        $queryResult = query($this->domain, $defaultDNS, true);
        $this->results['defaultDNS']['Varsayılan'] = $this->processQueryResult('Varsayılan', $defaultDNS, $queryResult);
    }

    public function checkCustomDNS()
    {
        $scriptDir = dirname(realpath(__FILE__));
        $customDNSFile = $scriptDir . "/CustomDNS.txt";

        if (file_exists($customDNSFile) && is_readable($customDNSFile)) {
            $customDNSContent = file_get_contents($customDNSFile);
            preg_match_all('/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/', $customDNSContent, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $dns) {
                    $queryResult = query($this->domain, $dns, true);
                    $this->results['customDNS'][$dns] = $this->processQueryResult($dns, $dns, $queryResult);
                }
            }
        }
    }

    public function getIntelLinks()
    {
        global $urls;
        $this->results['intelLinks'] = [
            "AlienVault Open Threat Exchange" => "https://otx.alienvault.com/indicator/domain/{$this->domain}",
            "Bitdefender TrafficLight" => "https://trafficlight.bitdefender.com/info/?url=https%3A%2F%2F{$this->domain}",
            "Google Safe Browsing" => "https://transparencyreport.google.com/safe-browsing/search?url={$this->domain}",
            "Kaspersky Threat Intelligence Portal" => "https://opentip.kaspersky.com/{$this->domain}?tab=web",
            "McAfee SiteAdvisor" => "https://siteadvisor.com/sitereport.html?url={$this->domain}",
            "Norton Safe Web" => "https://safeweb.norton.com/report/show?url={$this->domain}",
            "OpenDNS" => "https://domain.opendns.com/{$this->domain}",
            "URLVoid" => "https://www.urlvoid.com/scan/{$this->domain}/",
            "urlscan.io" => "https://urlscan.io/domain/{$this->domain}",
            "VirusTotal" => "https://www.virustotal.com/gui/domain/{$this->domain}/detection",
            "Whois.com" => "https://www.whois.com/whois/{$this->domain}",
            "Yandex Site safety report" => "https://yandex.com/safety/?l10n=en&url={$this->domain}"
        ];
    }

    public function getResults()
    {
        return $this->results;
    }
}

try {
    $domain = trim($_POST['domain']);

    // Domain validasyonu
    if (
        strlen($domain) > 253 ||
        !preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain) ||
        preg_match('/[a-zA-Z0-9-]{64,}/', $domain) ||
        strpos($domain, '--') !== false
    ) {
        throw new Exception('Geçersiz domain formatı! Lütfen "example.com" veya "sub.example.com" formatında girin.');
    }

    $checker = new DomainChecker($domain);

    // DNS kontrolleri
    $type = $_POST['type'];

    if ($type === 'all') {
        $checker->checkDNSGroup('nofilterDNS', $nofilterDNS);
        $checker->checkDNSGroup('secureDNS', $secureDNS);
        $checker->checkDNSGroup('adblockDNS', $adblockDNS);
        $checker->checkDefaultDNS();
        $checker->checkCustomDNS();
        $checker->getIntelLinks();
        echo json_encode($checker->getResults());
    } else {
        switch ($type) {
            case 'nofilterDNS':
                $checker->checkDNSGroup('nofilterDNS', $nofilterDNS);
                break;
            case 'secureDNS':
                $checker->checkDNSGroup('secureDNS', $secureDNS);
                break;
            case 'adblockDNS':
                $checker->checkDNSGroup('adblockDNS', $adblockDNS);
                break;
            case 'defaultDNS':
                $checker->checkDefaultDNS();
                break;
            case 'intelLinks':
                $checker->getIntelLinks();
                break;
        }
        echo json_encode([$type => $checker->getResults()[$type]]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
