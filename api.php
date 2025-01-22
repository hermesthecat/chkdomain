<?php

/**
 * Domain Check API
 * @author A. Kerem Gök
 */

require_once __DIR__ . 'includes/language.php';
require_once __DIR__ . 'includes/chkdm.php';

$lang = Language::getInstance();
header('Content-Type: application/json');

if (!isset($_POST['domain']) || !isset($_POST['type'])) {
    http_response_code(400);
    die(json_encode(['error' => $lang->get('error_domain_required') . ' & ' . $lang->get('error_type_required')]));
}

$validTypes = ['nofilterDNS', 'secureDNS', 'adblockDNS', 'defaultDNS', 'intelLinks', 'all'];
if (!in_array($_POST['type'], $validTypes)) {
    http_response_code(400);
    die(json_encode(['error' => $lang->get('error_invalid_type')]));
}

class DomainChecker
{
    private $domain;
    private $results = [];
    private $lang;

    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->lang = Language::getInstance();
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
                $response['message'] = $this->lang->get('status_failed');
                break;
            case 2:
                $response['status'] = 'error';
                $response['message'] = $this->lang->get('status_sinkhole');
                break;
            case 3:
                $response['status'] = 'error';
                $response['message'] = $this->lang->get('status_blockpage');
                break;
            case 4:
                $response['status'] = 'warning';
                $response['message'] = $this->lang->get('status_timeout');
                break;
            case 5:
                $response['status'] = 'warning';
                $response['message'] = $this->lang->get('status_refused');
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
        $this->results['defaultDNS'][$this->lang->get('default_dns_name')] = $this->processQueryResult($this->lang->get('default_dns_name'), $defaultDNS, $queryResult);
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
        throw new Exception($lang->get('error_invalid_domain'));
    }

    $checker = new DomainChecker($domain);
    $type = $_POST['type'];

    if ($type === 'all') {
        $checker->checkDNSGroup('nofilterDNS', $nofilterDNS);
        $checker->checkDNSGroup('secureDNS', $secureDNS);
        $checker->checkDNSGroup('adblockDNS', $adblockDNS);
        $checker->checkDefaultDNS();
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
