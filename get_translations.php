<?php
require_once __DIR__ . '/includes/language.php';
$lang = Language::getInstance();

// JavaScript için gerekli tüm çevirileri döndür
$translations = [
    'error_input' => $lang->get('error_domain_required'),
    'loading_placeholder' => $lang->get('loading_text'),
    'error_alert' => $lang->get('error_loading'),
    'error_query_failed' => $lang->get('error_query_failed')
];

header('Content-Type: application/json');
echo json_encode($translations);
