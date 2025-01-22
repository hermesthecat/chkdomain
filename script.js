document.addEventListener('DOMContentLoaded', function () {
    // Tema yönetimi
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedTheme = localStorage.getItem('theme') || (prefersDark ? 'dark' : 'light');

    // İlk tema ayarı
    if (savedTheme) {
        html.setAttribute('data-theme', savedTheme);
        themeToggle.querySelector('i').className = `fas fa-${savedTheme === 'dark' ? 'sun' : 'moon'}`;
    }

    // Tema değiştirme
    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        html.setAttribute('data-theme', newTheme);
        themeToggle.querySelector('i').className = `fas fa-${newTheme === 'dark' ? 'sun' : 'moon'}`;
        localStorage.setItem('theme', newTheme);
    });

    // Çeviri yönetimi
    let translations = {};
    function loadTranslations() {
        return $.get('get_translations.php').then(function (data) {
            translations = data;
        });
    }

    // İlk çevirileri yükle
    loadTranslations();

    // Dil değiştiğinde çevirileri güncelle
    $(document).on('click', '.lang-link', function () {
        setTimeout(loadTranslations, 100);
    });

    // DNS sorguları
    const queryTypes = ['nofilterDNS', 'secureDNS', 'adblockDNS', 'defaultDNS', 'intelLinks'];
    let currentDomain = '';
    let activeQueries = 0;

    $('#domainForm').on('submit', function (e) {
        e.preventDefault();
        currentDomain = $('#domain').val().trim();

        if (!currentDomain) {
            alert(translations.error_input || 'Please enter a domain name');
            return;
        }

        // UI'ı sıfırla
        $('.loading').show();
        $('#results').hide();
        queryTypes.forEach(type => {
            $(`#${type} .dns-results`).html(
                `<div class="loading-placeholder">${translations.loading_placeholder || 'Loading...'}</div>`
            );
        });
        $('#intelLinks .intel-links').html(
            `<div class="loading-placeholder">${translations.loading_placeholder || 'Loading...'}</div>`
        );

        // Sonuçları göster ve scroll
        $('#checkedDomain').text(currentDomain);
        $('#results').show();
        $('html, body').animate({
            scrollTop: $('#results').offset().top - 20
        }, 500);

        // Her DNS grubu için ayrı sorgu yap
        queryTypes.forEach((type, index) => {
            setTimeout(() => {
                loadDNSResults(type);
            }, index * 300);
        });
    });

    function loadDNSResults(type) {
        activeQueries++;
        updateLoadingStatus();

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                domain: currentDomain,
                type: type
            },
            dataType: 'json',
            success: function (response) {
                if (type === 'intelLinks') {
                    let intelHtml = '';
                    for (let name in response[type]) {
                        intelHtml += `<div class="dns-item">
                            <a href="${response[type][name]}" class="intel-link" target="_blank">
                                ${name} <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>`;
                    }
                    $('#intelLinks .intel-links').html(intelHtml);
                } else {
                    displayDNSResults(type, response[type]);
                }

                activeQueries--;
                updateLoadingStatus();
            },
            error: function (xhr, status, error) {
                $(`#${type} .dns-results`).html(
                    `<div class="dns-item">
                        <div class="dns-item-left">
                            <i class="fas fa-exclamation-circle status-error fa-lg"></i>
                            <span>Error</span>
                        </div>
                        <div class="dns-item-right">
                            ${translations.error_alert || 'Loading error'}: ${error}
                        </div>
                    </div>`
                );

                activeQueries--;
                updateLoadingStatus();
            }
        });
    }

    function updateLoadingStatus() {
        if (activeQueries === 0) {
            $('.loading').hide();
        }
    }

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
                <div class="dns-item-left">
                    <i class="fas ${icon} ${statusClass} fa-lg"></i>
                    <span class="dns-item-name">${name}</span>
                </div>
                <div class="dns-item-right">
                    ${status.ip ? `<div class="dns-item-ip">${status.ip}</div>` : ''}
                    ${status.message ? `<div class="dns-item-message">${status.message}</div>` : ''}
                </div>
            </div>`;
        }
        $(`#${groupId} .dns-results`).html(html);
    }
});
