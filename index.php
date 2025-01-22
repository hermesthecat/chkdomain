<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Checker - by A. Kerem Gök</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --warning-color: #f1c40f;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #2c3e50;
            --border-color: #edf2f7;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .result-box {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-top: 20px;
            padding: 24px;
            transition: all 0.3s ease;
        }

        .result-box:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .dns-group {
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            background: linear-gradient(to right bottom, rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.3));
            backdrop-filter: blur(10px);
        }

        .dns-group h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border-color);
        }

        .dns-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.5);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dns-item:hover {
            background-color: rgba(255, 255, 255, 0.8);
            transform: translateX(4px);
        }

        .dns-item:last-child {
            margin-bottom: 0;
        }

        .status-ok {
            color: var(--success-color);
        }

        .status-error {
            color: var(--error-color);
        }

        .status-warning {
            color: var(--warning-color);
        }

        .intel-links {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .intel-links::-webkit-scrollbar {
            width: 6px;
        }

        .intel-links::-webkit-scrollbar-track {
            background: var(--border-color);
            border-radius: 3px;
        }

        .intel-links::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
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
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 1.1em;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .domain-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
        }

        .check-button {
            border-radius: 12px;
            padding: 12px 32px;
            font-size: 1.1em;
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
        }

        .check-button:hover {
            background-color: #357abd;
            transform: translateY(-2px);
        }

        .dns-item-content {
            flex: 1;
        }

        .dns-item-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .dns-item-ip {
            color: #666;
            font-size: 0.9em;
        }

        .dns-item-message {
            margin-left: auto;
            color: #666;
        }

        .intel-link {
            text-decoration: none;
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 8px;
            display: block;
            transition: all 0.2s ease;
        }

        .intel-link:hover {
            background-color: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
        }

        .intel-link i {
            margin-left: 8px;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .dns-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .dns-item-message {
                margin-left: 0;
            }
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

                <div id="intelLinks" class="dns-group">
                    <h4><i class="fas fa-info-circle"></i> Domain Güvenlik Bilgileri</h4>
                    <div class="intel-links"></div>
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

                        // Intel linklerini göster
                        var intelHtml = '';
                        for (var name in response.intelLinks) {
                            intelHtml += `<div class="dns-item">
                                <a href="${response.intelLinks[name]}" class="intel-link" target="_blank">
                                    ${name} <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>`;
                        }
                        $('#intelLinks .intel-links').html(intelHtml);

                        $('.loading').hide();
                        $('#results').show();

                        // Sonuçlara smooth scroll
                        $('html, body').animate({
                            scrollTop: $('#results').offset().top - 20
                        }, 500);
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
                        <i class="fas ${icon} ${statusClass} fa-lg"></i>
                        <div class="dns-item-content">
                            <div class="dns-item-name">${name}</div>
                            <div class="dns-item-ip">${status.ip}</div>
                        </div>
                        <div class="dns-item-message">${status.message}</div>
                    </div>`;
                }
                $(`#${groupId} .dns-results`).html(html);
            }
        });
    </script>
</body>

</html>