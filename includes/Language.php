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
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
        } elseif (isset($_COOKIE[$this->cookieName]) && in_array($_COOKIE[$this->cookieName], $this->availableLangs)) {
            $this->currentLang = $_COOKIE[$this->cookieName];
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
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
                if (preg_match('/^([a-z]{2})\.php$/', $file, $matches)) {
                    $this->availableLangs[] = $matches[1];
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
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
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
        $html .= "<label>{$this->get('language')}:</label> ";
        foreach ($this->availableLangs as $lang) {
            $activeClass = $lang === $this->currentLang ? 'active' : '';
            $html .= "<a href='?lang={$lang}' class='lang-link {$activeClass}'>";
            $html .= $this->get("lang_{$lang}");
            $html .= '</a>';
        }
        $html .= '</div>';
        return $html;
    }
}
