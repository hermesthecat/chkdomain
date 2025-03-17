<?php
class Language
{
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'en'; // Varsayılan dil İngilizce
    private $availableLangs = [];
    private $cookieName = 'preferred_language';
    private $cookieExpiry = 2592000; // 30 gün

    private function __construct()
    {
        $this->loadAvailableLanguages();

        // Dil seçimi öncelik sırası:
        // 1. URL'den gelen lang parametresi (?lang=xx)
        // 2. Cookie'den gelen dil tercihi
        // 3. Varsayılan dil (en)
        if (isset($_GET['lang']) && in_array($_GET['lang'], $this->availableLangs)) {
            $this->currentLang = $_GET['lang'];
            setcookie($this->cookieName, $this->currentLang, time() + $this->cookieExpiry, '/');
        } elseif (isset($_COOKIE[$this->cookieName]) && in_array($_COOKIE[$this->cookieName], $this->availableLangs)) {
            $this->currentLang = $_COOKIE[$this->cookieName];
        }

        $this->loadTranslations();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setLanguage($lang)
    {
        if (in_array($lang, $this->availableLangs)) {
            $this->currentLang = $lang;
            setcookie($this->cookieName, $lang, time() + $this->cookieExpiry, '/');
            $this->loadTranslations();
            return true;
        }
        return false;
    }

    private function loadAvailableLanguages()
    {
        $langDir = dirname(__DIR__) . '/lang';
        if (is_dir($langDir)) {
            $files = scandir($langDir);
            foreach ($files as $file) {
                // Dosya adı güvenlik kontrolü
                if (preg_match('/^([a-z]{2})\.php$/', $file, $matches)) {
                    $fullPath = $langDir . '/' . $file;
                    // Dosya türü ve güvenlik kontrolleri
                    if (is_file($fullPath) && 
                        pathinfo($fullPath, PATHINFO_EXTENSION) === 'php' && 
                        mime_content_type($fullPath) === 'text/x-php' &&
                        filesize($fullPath) < 1048576) { // Max 1MB
                        $this->availableLangs[] = $matches[1];
                    }
                }
            }
            sort($this->availableLangs);

            // Eğer hiç dil dosyası bulunamazsa varsayılan olarak EN ve TR ekle
            if (empty($this->availableLangs)) {
                $this->availableLangs = ['en', 'tr'];
            }
        }
    }

    private function loadTranslations()
    {
        $langFile = dirname(__DIR__) . "/lang/{$this->currentLang}.php";
        if (file_exists($langFile) && 
            is_file($langFile) && 
            pathinfo($langFile, PATHINFO_EXTENSION) === 'php' &&
            mime_content_type($langFile) === 'text/x-php' &&
            filesize($langFile) < 1048576) { // Max 1MB
            $translations = require $langFile;
            if (is_array($translations)) {
                $this->translations = array_map('htmlspecialchars', $translations);
            }
        }
    }

    public function get($key, $default = '')
    {
        return $this->translations[$key] ?? $default;
    }

    public function getCurrentLang()
    {
        return $this->currentLang;
    }

    public function getAvailableLangs()
    {
        return $this->availableLangs;
    }

    public function getLanguageSelector()
    {
        $html = '<div class="language-selector">';
        $html .= "<label for='langSelect'>{$this->get('language')}:</label> ";
        $html .= "<select id='langSelect' class='form-select form-select-sm' onchange='window.location.href = \"?lang=\" + this.value'>";
        foreach ($this->availableLangs as $lang) {
            $selected = $lang === $this->currentLang ? 'selected' : '';
            $html .= "<option value='{$lang}' {$selected}>";
            $html .= $this->get("lang_{$lang}");
            $html .= '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        return $html;
    }
}
