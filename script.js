$(document).ready(function () {
    $('#domainForm').on('submit', function (e) {
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
            success: function (response) {
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
            error: function (xhr, status, error) {
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