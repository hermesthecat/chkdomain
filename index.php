<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Checker - by A. Kerem Gök</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .result-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            padding: 20px;
        }

        .dns-group {
            margin-bottom: 20px;
        }

        .dns-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .dns-item:last-child {
            border-bottom: none;
        }

        .status-ok {
            color: #28a745;
        }

        .status-error {
            color: #dc3545;
        }

        .status-warning {
            color: #ffc107;
        }

        .intel-links {
            max-height: 300px;
            overflow-y: auto;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
        }

        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .domain-input {
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1.2em;
        }

        .check-button {
            border-radius: 25px;
            padding: 10px 30px;
            font-size: 1.2em;
        }
    </style>
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
                        <button type="submit" class="btn btn-primary check-button">
                            <i class="fas fa-search"></i> Kontrol Et
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="results" style="display: none;">
            <div class="result-box">
                <h3 class="text-center mb-4">Sonuçlar: <span id="checkedDomain"></span></h3>

                <div id="nofilterDNS" class="dns-group">
                    <h4><i class="fas fa-globe"></i> Normal DNS Sunucuları</h4>
                    <div class="dns-results"></div>
                </div>

                <div id="secureDNS" class="dns-group">
                    <h4><i class="fas fa-shield-alt"></i> Güvenli DNS Sunucuları</h4>
                    <div class="dns-results"></div>
                </div>

                <div id="adblockDNS" class="dns-group">
                    <h4><i class="fas fa-ban"></i> Reklam Engelleyici DNS Sunucuları</h4>
                    <div class="dns-results"></div>
                </div>

                <div id="defaultDNS" class="dns-group">
                    <h4><i class="fas fa-home"></i> Varsayılan DNS Sunucusu</h4>
                    <div class="dns-results"></div>
                </div>

                <div id="customDNS" class="dns-group">
                    <h4><i class="fas fa-cog"></i> Özel DNS Sunucuları</h4>
                    <div class="dns-results"></div>
                </div>

                <div id="intelLinks" class="dns-group">
                    <h4><i class="fas fa-info-circle"></i> Domain Güvenlik Bilgileri</h4>
                    <div class="intel-links"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <h4>Domain kontrol ediliyor...</h4>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#domainForm').on('submit', function(e) {
                e.preventDefault();
                var domain = $('#domain').val().trim();

                if (!domain) {
                    alert('Lütfen bir domain adı girin.');
                    return;
                }

                $('.loading').show();
                $('#results').hide();

                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        domain: domain
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#checkedDomain').text(domain);

                        // DNS sonuçlarını göster
                        displayDNSResults('nofilterDNS', response.nofilterDNS);
                        displayDNSResults('secureDNS', response.secureDNS);
                        displayDNSResults('adblockDNS', response.adblockDNS);
                        displayDNSResults('defaultDNS', response.defaultDNS);
                        if (response.customDNS) {
                            displayDNSResults('customDNS', response.customDNS);
                        }

                        // Intel linklerini göster
                        var intelHtml = '';
                        for (var name in response.intelLinks) {
                            intelHtml += `<div class="dns-item">
                                <a href="${response.intelLinks[name]}" target="_blank">
                                    ${name} <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>`;
                        }
                        $('#intelLinks .intel-links').html(intelHtml);

                        $('.loading').hide();
                        $('#results').show();
                    },
                    error: function(xhr, status, error) {
                        $('.loading').hide();
                        alert('Hata oluştu: ' + error);
                    }
                });
            });

            function displayDNSResults(groupId, results) {
                var html = '';
                for (var name in results) {
                    var status = results[name];
                    var statusClass = 'status-ok';
                    var icon = 'fa-check-circle';

                    if (status.status === 'error') {
                        statusClass = 'status-error';
                        icon = 'fa-times-circle';
                    } else if (status.status === 'warning') {
                        statusClass = 'status-warning';
                        icon = 'fa-exclamation-circle';
                    }

                    html += `<div class="dns-item">
                        <i class="fas ${icon} ${statusClass}"></i>
                        <strong>${name}</strong>
                        <span class="text-muted">(${status.ip})</span>
                        <span class="ms-2">${status.message}</span>
                    </div>`;
                }
                $(`#${groupId} .dns-results`).html(html);
            }
        });
    </script>
</body>

</html>