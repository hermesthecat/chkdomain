<?php
return [
    // General
    'title' => 'ドメインチェッカー',
    'check_button' => '確認',
    'domain_placeholder' => 'example.com',
    'loading_text' => 'ドメインを確認中...',
    'results_title' => '結果',

    // DNS Groups
    'normal_dns' => '通常のDNSサーバー',
    'secure_dns' => 'セキュアDNSサーバー',
    'adblock_dns' => '広告ブロックDNSサーバー',
    'default_dns' => 'デフォルトDNSサーバー',
    'default_dns_name' => 'デフォルト',
    'custom_dns' => 'カスタムDNSサーバー',
    'security_info' => 'ドメインセキュリティ情報',

    // Status Messages
    'status_ok' => '✅',
    'status_failed' => '❌',
    'status_sinkhole' => '🚫 Palo Alto DNSシンクホールを検出！',
    'status_blockpage' => '🚫 NextDNSブロックページを検出！',
    'status_timeout' => '⚠️ 接続がタイムアウトしました...',
    'status_refused' => '⚠️ 接続が拒否されました...',

    // Error Messages
    'error_invalid_domain' => '無効なドメイン形式です！"example.com"または"sub.example.com"の形式で入力してください。',
    'error_domain_required' => 'ドメインパラメータが必要です',
    'error_type_required' => 'クエリタイプパラメータが必要です',
    'error_invalid_type' => '無効なクエリタイプです',
    'error_loading' => '読み込みエラー',
    'error_query_failed' => 'クエリが失敗しました',
    'error_command_not_found' => 'コマンドが見つかりません！',

    // Language Selection
    'language' => '言語',
    'lang_tr' => 'Türkçe',
    'lang_en' => 'English',
    'lang_de' => 'Deutsch',
    'lang_fr' => 'Français',
    'lang_es' => 'Español',
    'lang_it' => 'Italiano',
    'lang_ja' => '日本語',
    'lang_ru' => 'Русский',
    'lang_zh' => '中文',

    // Theme
    'theme_light' => 'ライトモード',
    'theme_dark' => 'ダークモード',
    'theme_toggle' => 'テーマを切り替え'
];
