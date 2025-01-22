<?php
require_once __DIR__ . '/includes/language.php';
$lang = Language::getInstance();

if (isset($_GET['lang'])) {
    $lang->setLanguage($_GET['lang']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLang(); ?>" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('title'); ?> - by A. Kerem Gök</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <button class="theme-toggle" id="themeToggle" aria-label="<?php echo $lang->get('theme_toggle'); ?>" title="<?php echo $lang->get('theme_toggle'); ?>">
        <i class="fas fa-moon"></i>
    </button>
    <div class="container py-5">
        <?php echo $lang->getLanguageSelector(); ?>
        <h1 class="text-center mb-4"><?php echo $lang->get('title'); ?></h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form id="domainForm" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control domain-input" id="domain"
                            placeholder="<?php echo $lang->get('domain_placeholder'); ?>"
                            required pattern="^([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$">
                        <button type="submit" class="btn check-button">
                            <i class="fas fa-search"></i> <?php echo $lang->get('check_button'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="results" style="display: none;">
            <div class="result-box">
                <h3 class="text-center mb-4"><?php echo $lang->get('results_title'); ?>: <span id="checkedDomain" class="text-primary"></span></h3>

                <div id="nofilterDNS" class="dns-group">
                    <h4><i class="fas fa-globe"></i> <?php echo $lang->get('normal_dns'); ?></h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="secureDNS" class="dns-group">
                    <h4><i class="fas fa-shield-alt"></i> <?php echo $lang->get('secure_dns'); ?></h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="adblockDNS" class="dns-group">
                    <h4><i class="fas fa-ban"></i> <?php echo $lang->get('adblock_dns'); ?></h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="defaultDNS" class="dns-group">
                    <h4><i class="fas fa-home"></i> <?php echo $lang->get('default_dns'); ?></h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="intelLinks" class="dns-group">
                    <h4><i class="fas fa-info-circle"></i> <?php echo $lang->get('security_info'); ?></h4>
                    <div class="intel-links">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden"><?php echo $lang->get('loading_text'); ?></span>
            </div>
            <h4><?php echo $lang->get('loading_text'); ?></h4>
        </div>
    </div>

</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

</html>