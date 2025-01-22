<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Checker - by A. Kerem Gök</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Domain Checker</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form id="domainForm" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control domain-input" id="domain"
                            placeholder="example.com" required pattern="^([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$">
                        <button type="submit" class="btn check-button">
                            <i class="fas fa-search"></i> Kontrol Et
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="results" style="display: none;">
            <div class="result-box">
                <h3 class="text-center mb-4">Sonuçlar: <span id="checkedDomain" class="text-primary"></span></h3>

                <div id="nofilterDNS" class="dns-group">
                    <h4><i class="fas fa-globe"></i> Normal DNS Sunucuları</h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="secureDNS" class="dns-group">
                    <h4><i class="fas fa-shield-alt"></i> Güvenli DNS Sunucuları</h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="adblockDNS" class="dns-group">
                    <h4><i class="fas fa-ban"></i> Reklam Engelleyici DNS Sunucuları</h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="defaultDNS" class="dns-group">
                    <h4><i class="fas fa-home"></i> Varsayılan DNS Sunucusu</h4>
                    <div class="dns-results">
                        <div class="loading-placeholder"></div>
                    </div>
                </div>

                <div id="intelLinks" class="dns-group">
                    <h4><i class="fas fa-info-circle"></i> Domain Güvenlik Bilgileri</h4>
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
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <h4>Domain kontrol ediliyor...</h4>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>