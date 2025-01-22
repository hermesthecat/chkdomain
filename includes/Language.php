<?php
class Language
{
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'tr';
    private $availableLangs = ['tr', 'en'];

    private function __construct()
    {
        session_start();
        $this->currentLang = $_SESSION['lang'] ?? 'tr';
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
            $_SESSION['lang'] = $lang;
            $this->loadTranslations();
            return true;
        }
        return false;
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
