# Domain Checker (chkdomain)

Domain adreslerini çeşitli DNS sunucuları üzerinden kontrol eden ve güvenlik bilgilerini gösteren bir PHP uygulaması.

## Özellikler

- Birden fazla DNS sunucusu üzerinden domain kontrolü
- Normal, güvenli ve reklam engelleyici DNS sunucuları desteği
- Özel DNS sunucuları desteği (CustomDNS.txt)
- Domain güvenlik bilgileri ve tarama servisleri
- Modern web arayüzü ve CLI desteği
- AJAX ile anlık sonuçlar
- Responsive tasarım

## Gereksinimler

- PHP 7.4 veya üzeri
- dig
- nslookup
- sed
- head
- awk
- sort
- dirname
- readlink

## Kurulum

1. Repoyu klonlayın:

```bash
git clone https://github.com/yourusername/chkdomain.git
cd chkdomain
```

2. Dosya izinlerini ayarlayın:

```bash
chmod +x chkdm.php
```

## Kullanım

### Web Arayüzü

1. PHP'nin dahili web sunucusunu başlatın:

```bash
php -S localhost:8000
```

2. Tarayıcınızdan `http://localhost:8000` adresine gidin
3. Domain adını girin ve "Kontrol Et" butonuna tıklayın
4. Sonuçları görüntüleyin:
   - Normal DNS sunucuları
   - Güvenli DNS sunucuları
   - Reklam engelleyici DNS sunucuları
   - Varsayılan DNS sunucusu
   - Özel DNS sunucuları (varsa)
   - Domain güvenlik bilgileri ve bağlantıları

### Komut Satırı (CLI)

```bash
./chkdm.php example.com
# veya
php chkdm.php example.com
```

## Özel DNS Sunucuları

Kendi DNS sunucularınızı eklemek için:

1. Proje dizininde `CustomDNS.txt` dosyası oluşturun
2. Her satıra bir DNS sunucusu IP adresi ekleyin
3. Yorum satırları için # kullanabilirsiniz

Örnek `CustomDNS.txt`:

```
# Türk Telekom DNS
212.175.192.166
212.175.192.167

# Google DNS
8.8.8.8
8.8.4.4
```

## Desteklenen DNS Sunucuları

### Normal DNS Sunucuları

- AdGuard (94.140.14.140)
- Cloudflare (1.1.1.1)
- Google (8.8.8.8)
- OpenDNS (208.67.222.2)
- Quad9 (9.9.9.10)
- ve diğerleri...

### Güvenli DNS Sunucuları

- CleanBrowsing (185.228.168.9)
- Cloudflare (1.1.1.2)
- Comodo (8.26.56.26)
- OpenDNS (208.67.222.222)
- Quad9 (9.9.9.9)
- ve diğerleri...

### Reklam Engelleyici DNS Sunucuları

- AdGuard (94.140.14.14)
- CONTROL D (76.76.2.2)
- dnsforge.de (176.9.93.198)
- ve diğerleri...

## Güvenlik Kontrolleri

Domain'ler aşağıdaki servisler üzerinden kontrol edilebilir:

- AlienVault OTX
- Bitdefender TrafficLight
- Google Safe Browsing
- Kaspersky Threat Intelligence
- McAfee SiteAdvisor
- Norton Safe Web
- OpenDNS
- URLVoid
- urlscan.io
- VirusTotal
- Whois.com
- Yandex Site Safety

## Lisans

Bu proje GPL-3.0 lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## Yazar

A. Kerem Gök

## Katkıda Bulunanlar

- Peter Dave Hello (Orijinal bash script)
