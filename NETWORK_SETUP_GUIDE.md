# 🌐 Network Setup - Simultane Çalışma Rehberi

**Versiyon:** 1.0.0  
**Amaç:** Birden fazla kişinin aynı anda aynı database'i kullanarak sistemi test etmesi

---

## 📋 İÇİNDEKİLER

1. [Genel Bakış](#genel-bakış)
2. [Yöntem 1: Lokal Network (LAN)](#yöntem-1-lokal-network-lan)
3. [Yöntem 2: Ngrok (İnternet Üzerinden)](#yöntem-2-ngrok-internet-üzerinden)
4. [Yöntem 3: Herkes Kendi Lokal'inde](#yöntem-3-herkes-kendi-lokalinde)
5. [Roller ve Test Senaryosu](#roller-ve-test-senaryosu)

---

## 🎯 Genel Bakış

### Seçenekler:

| Yöntem | Zorluk | Hız | Simultane | Önerilen |
|--------|--------|-----|-----------|----------|
| **LAN (Aynı WiFi)** | Kolay | ⚡⚡⚡ Çok Hızlı | ✅ Evet | ⭐⭐⭐ |
| **Ngrok (İnternet)** | Orta | ⚡⚡ Orta | ✅ Evet | ⭐⭐ |
| **Herkes Kendi Lokal'i** | Çok Kolay | ⚡⚡⚡ Hızlı | ❌ Hayır | ⭐ (Test için) |

---

## 🏠 Yöntem 1: Lokal Network (LAN)

**Senaryo:** Aynı WiFi/network'te olan arkadaşlarınız sizin bilgisayarınıza bağlanır.

### ✅ Avantajlar:
- Gerçek simultane çalışma
- Çok hızlı
- Kolay kurulum
- İnternet gerekmez

### ❌ Dezavantajlar:
- Aynı WiFi/network'te olmanız gerekir
- Bir kişinin bilgisayarı "server" olur (her zaman açık olmalı)

---

### 📝 Adımlar (LAN Setup)

#### 1️⃣ Host (Server) Olacak Kişi

**A. IP Adresinizi Öğrenin**

**Mac:**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```
Örnek çıktı:
```
inet 192.168.1.105 netmask 0xffffff00 broadcast 192.168.1.255
```
IP adresiniz: **`192.168.1.105`**

**Windows:**
```cmd
ipconfig
```
"IPv4 Address" satırını bulun:
```
IPv4 Address: 192.168.1.105
```

**B. MySQL'i Network'e Açın**

**1. MySQL Config Dosyasını Bulun:**

**AMPPS (Mac):**
```bash
/Applications/AMPPS/mysql/etc/my.cnf
```

**AMPPS (Windows):**
```
C:\Program Files\Ampps\mysql\etc\my.cnf
```

**XAMPP (Mac/Linux):**
```bash
/Applications/XAMPP/xamppfiles/etc/my.cnf
```

**XAMPP (Windows):**
```
C:\xampp\mysql\bin\my.ini
```

**2. Dosyayı Düzenleyin:**

Şu satırı bulun:
```ini
bind-address = 127.0.0.1
```

Şu şekilde değiştirin (ya da comment out):
```ini
# bind-address = 127.0.0.1
bind-address = 0.0.0.0
```

**3. MySQL'i Yeniden Başlatın:**
- AMPPS/XAMPP Control Panel → MySQL → Stop → Start

**C. MySQL Kullanıcısı Oluşturun (Network Erişimi İçin)**

1. **phpMyAdmin'i açın:**
   ```
   http://localhost/phpmyadmin/
   ```

2. **SQL sekmesine gidin, şu komutu çalıştırın:**
   ```sql
   CREATE USER 'campus_user'@'%' IDENTIFIED BY 'campus2024';
   GRANT ALL PRIVILEGES ON campus_marketplace.* TO 'campus_user'@'%';
   FLUSH PRIVILEGES;
   ```

   **Açıklama:**
   - `campus_user`: Kullanıcı adı
   - `campus2024`: Şifre
   - `%`: Herhangi bir IP'den bağlanabilir

**D. Firewall Ayarları**

**Mac:**
1. System Preferences → Security & Privacy → Firewall
2. "Firewall Options"
3. MySQL (port 3306) için "Allow incoming connections"

**Windows:**
1. Windows Defender Firewall → Advanced Settings
2. Inbound Rules → New Rule
3. Port → TCP → 3306 → Allow

**E. Apache'yi Network'e Açın**

`httpd.conf` dosyasını düzenleyin:

**AMPPS:**
```
/Applications/AMPPS/apache/conf/httpd.conf  (Mac)
C:\Program Files\Ampps\apache\conf\httpd.conf  (Windows)
```

Şu satırı bulun:
```apache
Listen 127.0.0.1:80
```

Şu şekilde değiştirin:
```apache
Listen 0.0.0.0:80
```

Ya da basitçe:
```apache
Listen 80
```

Apache'yi yeniden başlatın.

---

#### 2️⃣ İstemci (Client) Olacak Kişiler

**A. config/db.php Dosyasını Düzenleyin**

1. Projeyi bilgisayarınıza kurun (`INSTALLATION_GUIDE.md`'yi takip edin)

2. `config/db.php` dosyasını açın

3. Şu kısmı değiştirin:
   ```php
   $dbConfig = [
       'host'    => '192.168.1.105',  // Host'un IP adresi
       'name'    => 'campus_marketplace',
       'user'    => 'campus_user',     // Oluşturduğumuz kullanıcı
       'pass'    => 'campus2024',      // Şifre
       'charset' => 'utf8mb4',
   ];
   
   $dsn = sprintf(
       'mysql:host=%s;dbname=%s;charset=%s',
       $dbConfig['host'],
       $dbConfig['name'],
       $dbConfig['charset']
   );
   ```

4. Kaydedin.

**B. config/config.php'yi Düzenleyin (Opsiyonel)**

Eğer Host'un Apache'si de paylaşılacaksa:

```php
// Manuel olarak host'un IP'sini ayarlayın
define('BASE_URL', 'http://192.168.1.105/campus-marketplace');

function url(string $path = ''): string {
    return BASE_URL . $path;
}
```

Ya da kendi lokal Apache'nizi kullanıp sadece database'e bağlanabilirsiniz (önerilir).

---

#### 3️⃣ Bağlantıyı Test Etme

**İstemci bilgisayarda:**

1. Terminal/CMD açın
2. MySQL bağlantısını test edin:
   ```bash
   mysql -h 192.168.1.105 -u campus_user -pcampus2024 campus_marketplace -e "SELECT COUNT(*) FROM User;"
   ```

3. Başarılıysa şunu göreceksiniz:
   ```
   +----------+
   | COUNT(*) |
   +----------+
   |       17 |
   +----------+
   ```

4. Tarayıcıda siteyi açın:
   ```
   http://localhost/campus-marketplace/
   ```

5. Login yapın ve test edin!

---

## 🌐 Yöntem 2: Ngrok (İnternet Üzerinden)

**Senaryo:** Farklı lokasyonlardaki arkadaşlarınız sizin bilgisayarınıza internet üzerinden bağlanır.

### ✅ Avantajlar:
- Farklı lokasyonlardan erişim
- Gerçek simultane çalışma
- Demo için harika

### ❌ Dezavantajlar:
- Biraz yavaş olabilir
- Ngrok hesabı gerekir (ücretsiz)
- Host bilgisayar her zaman açık olmalı

---

### 📝 Adımlar (Ngrok Setup)

#### 1️⃣ Ngrok Kurulumu

1. **Ngrok'a kaydolun:** [https://ngrok.com/](https://ngrok.com/)

2. **Ngrok'u indirin:**
   - Mac: `brew install ngrok`
   - Windows: [ngrok.com/download](https://ngrok.com/download) → ZIP indirin

3. **Auth token'ı alın:**
   - Dashboard → "Your Authtoken"
   - Kopyalayın

4. **Auth token'ı kaydedin:**
   ```bash
   ngrok config add-authtoken YOUR_AUTHTOKEN
   ```

#### 2️⃣ Apache'yi Ngrok ile Paylaşma

**Terminal'de çalıştırın:**
```bash
ngrok http 80
```

**Çıktı:**
```
Session Status    online
Account           your_email@example.com
Forwarding        https://abc123.ngrok.io -> http://localhost:80
```

**Public URL:** `https://abc123.ngrok.io`

Bu URL'i arkadaşlarınıza verin!

#### 3️⃣ MySQL'i Ngrok ile Paylaşma (Ayrı Terminal)

**İkinci bir terminal açın:**
```bash
ngrok tcp 3306
```

**Çıktı:**
```
Forwarding        tcp://0.tcp.ngrok.io:12345 -> localhost:3306
```

**MySQL Bağlantı Bilgileri:**
- **Host:** `0.tcp.ngrok.io`
- **Port:** `12345`
- **User:** `campus_user`
- **Password:** `campus2024`

#### 4️⃣ İstemciler İçin Config

**config/db.php:**
```php
$dbConfig = [
    'host'    => '0.tcp.ngrok.io',
    'port'    => 12345,  // Ngrok'tan alınan port
    'name'    => 'campus_marketplace',
    'user'    => 'campus_user',
    'pass'    => 'campus2024',
    'charset' => 'utf8mb4',
];

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['name'],
    $dbConfig['charset']
);
```

**Site URL:**
```
https://abc123.ngrok.io/campus-marketplace/
```

---

## 💻 Yöntem 3: Herkes Kendi Lokal'inde

**Senaryo:** Herkes kendi bilgisayarında ayrı bir instance çalıştırır.

### ✅ Avantajlar:
- En kolay kurulum
- Herkes kendi hızında çalışır
- Network gerektirmez

### ❌ Dezavantajlar:
- Gerçek simultane değil
- Herkes ayrı database kullanır
- Değişiklikler paylaşılmaz

---

### 📝 Adımlar

1. **Herkes `INSTALLATION_GUIDE.md`'yi takip eder**
2. **Herkes kendi lokal'inde kurulum yapar**
3. **Test için:**
   - Ahmet → Student rolü ile kendi lokal'inde çalışır
   - Ayşe → Moderator rolü ile kendi lokal'inde çalışır
   - Admin → Admin rolü ile kendi lokal'inde çalışır

**Not:** Bu yöntem demo pratiği için idealdir ama gerçek simultane çalışma değildir.

---

## 🎭 Roller ve Test Senaryosu

### Önerilen Rol Dağılımı (4 Kişi İçin):

| Kişi | Rol | Test Kullanıcısı | Görevler |
|------|-----|-----------------|---------|
| **Kişi 1** | Admin | `admin@istun.edu.tr` | Kategori yönetimi, User yönetimi |
| **Kişi 2** | Moderator | `ayse.kara@istun.edu.tr` | Listing onaylama, Şikayet yönetimi |
| **Kişi 3** | Student (Seller) | `ahmet.yilmaz@istun.edu.tr` | Listing oluşturma, Bid kabul etme |
| **Kişi 4** | Student (Buyer) | `elif.ozturk@istun.edu.tr` | Bid verme, Mesaj gönderme, Review bırakma |

### 🎬 Simultane Test Senaryosu (10 Dakika):

**Dakika 1-2: Herkes Login Olur**
- Kişi 1: Admin olarak login
- Kişi 2: Moderator olarak login
- Kişi 3: Student (Seller) olarak login
- Kişi 4: Student (Buyer) olarak login

**Dakika 3-4: Seller Listing Oluşturur**
- Kişi 3 (Seller): "+ Listing" → Yeni ürün ekler (örn: "iPhone 12")
- Status: **Pending** (Moderator onayı bekliyor)

**Dakika 5: Moderator Onaylar**
- Kişi 2 (Moderator): "Manage" → Pending listing'i görür
- Status'ü **Active** yapar
- **Trigger çalışır:** Seller'a notification gider! 🔔

**Dakika 6: Seller Notification Görür**
- Kişi 3 (Seller): Navbar'da notification badge'i görür
- "Your listing has been approved" mesajını okur ✅

**Dakika 7: Buyer Bid Verir**
- Kişi 4 (Buyer): Aktif listing'i görür
- "Place Bid" → 5000 TL teklif verir
- **Trigger çalışır:** Seller'a notification gider! 🔔

**Dakika 8: Seller Bid'i Kabul Eder**
- Kişi 3 (Seller): Notification'ı görür → "New bid on your listing"
- Listing detail → "Accept Bid" tıklar
- Status: **Sold** olur
- **Trigger çalışır:** Buyer'a notification gider! 🔔

**Dakika 9: Buyer Review Bırakır**
- Kişi 4 (Buyer): Profile → "Users You Can Review"
- Seller için 5 yıldız + yorum bırakır
- **Interaction record oluşur**

**Dakika 10: Admin İstatistikleri Görür**
- Kişi 1 (Admin): Dashboard'da güncel istatistikleri görür
- Listings, Bids, Reviews sayıları artmış olmalı 📊

**✅ Test Başarılı:** Tüm roller simultane çalıştı, triggers ve notifications çalıştı!

---

## 🐛 Sorun Giderme

### 1. "Connection Refused" Hatası

**Sebep:** Firewall veya MySQL network'te değil.

**Çözüm:**
- MySQL'in `bind-address = 0.0.0.0` olduğundan emin olun
- Firewall'da port 3306'nın açık olduğunu kontrol edin
- MySQL'i restart edin

### 2. "Access Denied for User" Hatası

**Sebep:** Network kullanıcısı oluşturulmamış.

**Çözüm:**
```sql
CREATE USER 'campus_user'@'%' IDENTIFIED BY 'campus2024';
GRANT ALL PRIVILEGES ON campus_marketplace.* TO 'campus_user'@'%';
FLUSH PRIVILEGES;
```

### 3. Ngrok "Too Many Connections" Hatası

**Sebep:** Ngrok ücretsiz plan limiti.

**Çözüm:**
- Ngrok'u restart edin
- Ya da LAN yöntemini kullanın

### 4. "Mixed Content" Hatası (Ngrok HTTPS)

**Sebep:** HTTP içeriği HTTPS sayfada yüklenemiyor.

**Çözüm:**
- `config/config.php`'de `https://` kullanın
- Ya da tarayıcıda "insecure content" ayarını açın

---

## 📊 Performans İpuçları

### LAN İçin:
- ⚡ En hızlı yöntem
- Gecikme: ~1-5ms
- Önerilen: 2-5 kişi

### Ngrok İçin:
- ⚡ Orta hızlı
- Gecikme: ~50-200ms
- Önerilen: 2-3 kişi

### Lokal İçin:
- ⚡ Çok hızlı
- Simultane değil
- Pratik için ideal

---

## ✅ Özet

| Durum | Öneri |
|-------|-------|
| **Aynı yerde (okul/ev)** | LAN (Yöntem 1) ⭐⭐⭐ |
| **Farklı yerlerde** | Ngrok (Yöntem 2) ⭐⭐ |
| **Sadece pratik** | Herkes kendi lokal'i (Yöntem 3) ⭐ |

---

## 🎉 İyi Eğlenceler!

Artık simultane çalışabilirsiniz! Demo için bol şanslar! 🚀

---

**Son Güncelleme:** 17 Aralık 2024  
**Versiyon:** 1.0.0  
**Hazırlayan:** Ümmügülsün Türkmen (230611056)


