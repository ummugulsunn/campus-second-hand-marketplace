# 📦 Campus Marketplace - Kurulum Rehberi

**Versiyon:** 1.0.0  
**Son Güncelleme:** 17 Aralık 2024

Bu rehber, Campus Second-Hand Marketplace projesini bilgisayarınıza kurmak için gerekli tüm adımları içerir.

---

## 📋 İÇİNDEKİLER

1. [Sistem Gereksinimleri](#sistem-gereksinimleri)
2. [Kurulum Seçenekleri](#kurulum-seçenekleri)
3. [Adım Adım Kurulum (AMPPS)](#adım-adım-kurulum-ampps)
4. [Adım Adım Kurulum (XAMPP)](#adım-adım-kurulum-xampp)
5. [Adım Adım Kurulum (MAMP - Mac)](#adım-adım-kurulum-mamp-mac)
6. [Database Kurulumu](#database-kurulumu)
7. [Projeyi Çalıştırma](#projeyi-çalıştırma)
8. [Test Kullanıcıları](#test-kullanıcıları)
9. [Sorun Giderme](#sorun-giderme)

---

## 🖥️ Sistem Gereksinimleri

### Minimum Gereksinimler:
- ✅ **PHP:** 8.0 veya üzeri
- ✅ **MySQL:** 5.7 veya üzeri (8.0+ önerilir)
- ✅ **Web Server:** Apache veya PHP Built-in Server
- ✅ **Tarayıcı:** Chrome, Firefox, Safari (güncel versiyonlar)
- ✅ **Disk Alanı:** ~50 MB
- ✅ **RAM:** Minimum 2 GB

### Önerilen:
- ⭐ **PHP:** 8.1+
- ⭐ **MySQL:** 8.0+
- ⭐ **RAM:** 4 GB+

---

## 🎯 Kurulum Seçenekleri

Üç farklı yöntemle kurulum yapabilirsiniz:

| Yöntem | Platform | Zorluk | Önerilen |
|--------|----------|--------|----------|
| **AMPPS** | Windows, Mac, Linux | Kolay | ⭐⭐⭐ |
| **XAMPP** | Windows, Mac, Linux | Kolay | ⭐⭐⭐ |
| **MAMP** | Mac, Windows | Kolay | ⭐⭐ |

**Not:** Bu rehberde her üç yöntem de anlatılmıştır.

---

## 📦 Adım Adım Kurulum (AMPPS)

### 1. AMPPS İndirme ve Kurma

**Windows için:**
1. [AMPPS Download](http://ampps.com/downloads/) sayfasına gidin
2. "AMPPS for Windows" indirin
3. İndirilen `.exe` dosyasını çalıştırın
4. Kurulum adımlarını takip edin
5. Varsayılan ayarları kabul edin

**Mac için:**
1. [AMPPS Download](http://ampps.com/downloads/) sayfasına gidin
2. "AMPPS for Mac" indirin (`.dmg` dosyası)
3. `.dmg` dosyasını açın ve AMPPS'i Applications'a sürükleyin
4. AMPPS'i açın ve kurulum adımlarını takip edin

### 2. AMPPS'i Başlatma

1. **AMPPS Control Panel'i açın**
   - Windows: Start Menu → AMPPS
   - Mac: Applications → AMPPS

2. **Apache ve MySQL'i başlatın**
   - "Apache" yanındaki **Start** butonuna tıklayın
   - "MySQL" yanındaki **Start** butonuna tıklayın
   - Her ikisi de **yeşil** olmalı ✅

### 3. Projeyi AMPPS'e Ekleme

1. **Proje klasörünü bulun:**
   ```
   Windows: C:\Program Files\Ampps\www\
   Mac: /Applications/AMPPS/www/
   ```

2. **GitHub'dan projeyi indirin:**
   ```bash
   cd /path/to/Ampps/www/
   git clone https://github.com/ummugulsunn/campus-second-hand-marketplace.git
   cd campus-second-hand-marketplace
   ```
   
   Ya da manuel:
   - ZIP olarak indirin
   - `www/campus-second-hand-marketplace/` klasörüne çıkartın

3. **Klasör yapısını kontrol edin:**
   ```
   www/
   └── campus-second-hand-marketplace/
       ├── config/
       ├── includes/
       ├── pages/
       ├── index.php
       └── projectdb_export.sql
   ```

### 4. `.htaccess` Dosyasını Kontrol Etme

Proje klasöründe `.htaccess` dosyası olmalı. İçeriği:

```apache
# Set the base path for the application
RewriteEngine On
RewriteBase /campus-marketplace/

# Rewrite absolute paths to include base directory
RewriteCond %{REQUEST_URI} !^/campus-marketplace/
RewriteCond %{REQUEST_URI} ^/(pages|assets|logout\.php|index\.php)
RewriteRule ^(.*)$ /campus-marketplace/$1 [L]

# Set environment variable for PHP to use
SetEnv BASE_PATH /campus-marketplace
```

**Not:** Eğer farklı bir klasör adı kullanıyorsanız, `/campus-marketplace/` kısmını değiştirin.

---

## 📦 Adım Adım Kurulum (XAMPP)

### 1. XAMPP İndirme ve Kurma

**Windows/Mac/Linux için:**
1. [XAMPP Download](https://www.apachefriends.org/download.html) sayfasına gidin
2. İşletim sisteminize uygun versiyonu indirin (PHP 8.0+)
3. Kurulumu çalıştırın
4. Apache ve MySQL seçeneklerini işaretleyin

### 2. XAMPP'i Başlatma

1. **XAMPP Control Panel'i açın**
2. **Apache** → **Start** tıklayın
3. **MySQL** → **Start** tıklayın
4. Her ikisi de **yeşil** olmalı ✅

### 3. Projeyi XAMPP'e Ekleme

1. **Proje klasörünü bulun:**
   ```
   Windows: C:\xampp\htdocs\
   Mac: /Applications/XAMPP/htdocs/
   Linux: /opt/lampp/htdocs/
   ```

2. **Projeyi kopyalayın:**
   ```bash
   cd /path/to/xampp/htdocs/
   git clone https://github.com/ummugulsunn/campus-second-hand-marketplace.git
   cd campus-second-hand-marketplace
   ```

3. **`.htaccess` dosyasını kontrol edin** (yukarıdaki AMPPS bölümüne bakın)

---

## 📦 Adım Adım Kurulum (MAMP - Mac)

### 1. MAMP İndirme ve Kurma

1. [MAMP Download](https://www.mamp.info/en/downloads/) sayfasına gidin
2. **MAMP (ücretsiz)** versiyonunu indirin
3. `.pkg` dosyasını çalıştırın
4. Kurulum adımlarını takip edin

### 2. MAMP'i Başlatma

1. **MAMP uygulamasını açın**
2. **Start Servers** butonuna tıklayın
3. Apache ve MySQL yeşil olmalı ✅

### 3. Projeyi MAMP'e Ekleme

1. **Proje klasörünü bulun:**
   ```
   /Applications/MAMP/htdocs/
   ```

2. **Projeyi kopyalayın:**
   ```bash
   cd /Applications/MAMP/htdocs/
   git clone https://github.com/ummugulsunn/campus-second-hand-marketplace.git
   cd campus-second-hand-marketplace
   ```

---

## 🗄️ Database Kurulumu

### Adım 1: phpMyAdmin'e Giriş

1. **Tarayıcınızda açın:**
   - AMPPS: `http://localhost/phpmyadmin/`
   - XAMPP: `http://localhost/phpmyadmin/`
   - MAMP: `http://localhost:8888/phpMyAdmin/` (port değişebilir)

2. **Giriş bilgileri:**
   - **Username:** `root`
   - **Password:** (boş bırakın ya da MAMP için `root`)

### Adım 2: Database Oluşturma

1. **Sol tarafta "New" (Yeni) butonuna tıklayın**

2. **Database adı:**
   ```
   campus_marketplace
   ```

3. **Collation:**
   ```
   utf8mb4_unicode_ci
   ```

4. **"Create" (Oluştur) butonuna tıklayın**

### Adım 3: SQL Dosyasını İçe Aktarma

1. **Oluşturduğunuz `campus_marketplace` database'ine tıklayın**

2. **Üst menüden "Import" (İçe Aktar) sekmesine tıklayın**

3. **"Choose File" (Dosya Seç) butonuna tıklayın**

4. **Proje klasöründen şu dosyayı seçin:**
   ```
   projectdb_export.sql
   ```

5. **"Go" (Çalıştır) butonuna tıklayın**

6. **Başarılı mesajı görünmeli:** ✅
   ```
   Import has been successfully finished
   ```

### Adım 4: Database İçeriğini Kontrol Etme

1. **Sol tarafta `campus_marketplace` database'ine tıklayın**

2. **11 tablo görünmeli:**
   - `User` (17 kullanıcı)
   - `Role` (3 rol)
   - `Category` (5 kategori)
   - `Product_Listing` (24 ilan)
   - `Bid` (27 teklif)
   - `Message` (2 mesaj)
   - `Review` (16 değerlendirme)
   - `Complaint_Report` (13 şikayet)
   - `Notification` (33 bildirim)
   - `Saved_Item` (24 kayıtlı ürün)
   - `Interaction` (ilişki tablosu)

3. **Stored Procedures kontrol:**
   - Sol menüde "Routines" (Yordamlar) sekmesine tıklayın
   - **15 stored procedure** görünmeli ✅

4. **Triggers kontrol:**
   - Bir tabloya tıklayın (örn: `Bid`)
   - "Triggers" sekmesine tıklayın
   - **5 trigger** görünmeli (çeşitli tablolarda) ✅

---

## 🚀 Projeyi Çalıştırma

### 1. Tarayıcıda Siteyi Açma

**AMPPS/XAMPP için:**
```
http://localhost/campus-second-hand-marketplace/
```

**MAMP için:**
```
http://localhost:8888/campus-second-hand-marketplace/
```

### 2. Ana Sayfa Kontrolü

Açılan sayfada şunlar görünmeli:
- ✅ "Campus Market" başlığı
- ✅ "Login" ve "Register" butonları
- ✅ "Latest Listings" bölümü
- ✅ İstatistikler (17+ Active Users, 15+ Active Listings, 5 Categories)

### 3. İlk Giriş

**Test için hazır admin hesabı:**
- **Email:** `admin@istun.edu.tr`
- **Password:** `password`

1. Sağ üstteki **"Login"** butonuna tıklayın
2. Email ve şifreyi girin
3. **"Login"** butonuna tıklayın
4. Ana sayfaya yönlendirilmelisiniz
5. Sağ üstte **"Admin"** butonu görünmeli ✅

---

## 👥 Test Kullanıcıları

Proje, farklı rollerde hazır test kullanıcıları ile geliyor:

### 🔑 Admin Hesapları
| Email | Şifre | Rol |
|-------|-------|-----|
| `admin@istun.edu.tr` | `password` | Admin |
| `mehmet.demir@istun.edu.tr` | `password` | Admin |

### 👮 Moderator Hesapları
| Email | Şifre | Rol |
|-------|-------|-----|
| `ayse.kara@istun.edu.tr` | `password` | Moderator |
| `fatma.yildiz@istun.edu.tr` | `password` | Moderator |

### 👨‍🎓 Student Hesapları
| Email | Şifre | Rol |
|-------|-------|-----|
| `ahmet.yilmaz@istun.edu.tr` | `password` | Student |
| `elif.ozturk@istun.edu.tr` | `password` | Student |
| `can.arslan@istun.edu.tr` | `password` | Student |
| `zeynep.celik@istun.edu.tr` | `password` | Student |

**Not:** Tüm şifreler `password_hash()` ile güvenli şekilde hashlenmiştir.

---

## 🎯 Kurulum Doğrulama

### Kontrol Listesi:

- [ ] Apache/Web server çalışıyor
- [ ] MySQL çalışıyor
- [ ] phpMyAdmin'e erişilebiliyor
- [ ] `campus_marketplace` database'i oluşturuldu
- [ ] 11 tablo import edildi
- [ ] 15 stored procedure var
- [ ] 5 trigger var
- [ ] Site ana sayfası açılıyor
- [ ] Login çalışıyor
- [ ] Admin panel erişilebiliyor

### Test Senaryosu:

1. **Admin olarak giriş yapın**
   ```
   Email: admin@istun.edu.tr
   Password: password
   ```

2. **Admin Dashboard'a gidin**
   - Sağ üstte "Admin" butonu → tıklayın
   - İstatistikler görünmeli (Users, Listings, Bids, etc.)

3. **Yeni kategori oluşturun**
   - "Manage Categories" → tıklayın
   - "Add New Category" → kategori adı girin (örn: "Sports")
   - "Add Category" → tıklayın
   - Başarı mesajı görünmeli ✅

4. **Student olarak giriş yapın**
   - Logout → Login
   ```
   Email: ahmet.yilmaz@istun.edu.tr
   Password: password
   ```

5. **Yeni listing oluşturun**
   - "+ Listing" butonu → tıklayın
   - Form doldurun
   - "Create Listing" → tıklayın
   - Başarı mesajı + yönlendirme ✅

**Tüm adımlar başarılıysa → ✅ Kurulum tamamlandı!**

---

## 🐛 Sorun Giderme

### 1. "Database connection failed" Hatası

**Sebep:** MySQL çalışmıyor veya bağlantı bilgileri yanlış.

**Çözüm:**
1. AMPPS/XAMPP/MAMP Control Panel'de MySQL'in çalıştığını kontrol edin
2. `config/db.php` dosyasını kontrol edin:
   ```php
   'host' => 'localhost',
   'name' => 'campus_marketplace',
   'user' => 'root',
   'pass' => '',  // MAMP için 'root' deneyin
   ```

3. MAMP kullanıyorsanız, `db.php`'de şunu değiştirin:
   ```php
   $dsn = sprintf(
       'mysql:host=%s;dbname=%s;charset=%s;port=8889',  // MAMP port'u
       $dbConfig['host'],
       $dbConfig['name'],
       $dbConfig['charset']
   );
   ```

### 2. "404 Not Found" Hatası

**Sebep:** `.htaccess` çalışmıyor veya base path yanlış.

**Çözüm:**
1. Apache'de `mod_rewrite` modülünün aktif olduğundan emin olun
2. `.htaccess` dosyasının proje kök dizininde olduğunu kontrol edin
3. `RewriteBase` yolunu kontrol edin:
   ```apache
   RewriteBase /campus-second-hand-marketplace/
   ```
   (Klasör adınız farklıysa değiştirin)

### 3. "Access Denied" (Giriş Reddedildi) Hatası

**Sebep:** MySQL kullanıcı adı veya şifresi yanlış.

**Çözüm:**
1. phpMyAdmin'e giriş yapmayı deneyin (aynı kullanıcı adı/şifre)
2. MAMP kullanıyorsanız şifre `root` olabilir
3. `config/db.php`'de şifreyi güncelleyin

### 4. Sayfalar Görünüyor Ama CSS/JavaScript Yüklenmiyor

**Sebep:** Base path veya assets yolu yanlış.

**Çözüm:**
1. Tarayıcıda sağ tık → "Inspect" → "Console" sekmesi
2. 404 hatalarını kontrol edin
3. `config/config.php`'deki `BASE_URL`'i kontrol edin
4. Hard refresh yapın: `Cmd+Shift+R` (Mac) veya `Ctrl+Shift+R` (Windows)

### 5. "500 Internal Server Error" Hatası

**Sebep:** PHP syntax hatası veya izin sorunu.

**Çözüm:**
1. Apache error log'unu kontrol edin:
   - AMPPS: `Ampps/apache/logs/error_log`
   - XAMPP: `xampp/apache/logs/error_log`
   - MAMP: `MAMP/logs/apache_error.log`

2. Proje dosyalarının okuma iznine sahip olduğunu kontrol edin
3. PHP versiyonunun 8.0+ olduğunu kontrol edin

### 6. Stored Procedures Çalışmıyor

**Sebep:** Stored procedures import edilmemiş.

**Çözüm:**
1. phpMyAdmin → `campus_marketplace` database
2. "Routines" sekmesi → 15 procedure olmalı
3. Yoksa, terminal'de şunu çalıştırın:
   ```bash
   mysql -u root campus_marketplace < stored_procedures.sql
   ```

### 7. Triggers Çalışmıyor (Notifications oluşmuyor)

**Sebep:** Triggers import edilmemiş.

**Çözüm:**
1. phpMyAdmin → Bir tablo seç → "Triggers" sekmesi
2. Trigger yoksa, terminal'de:
   ```bash
   mysql -u root campus_marketplace < triggers.sql
   ```

---

## 📧 Destek

Sorun yaşıyorsanız:

1. **GitHub Issues:** [Buraya](https://github.com/ummugulsunn/campus-second-hand-marketplace/issues) sorun bildirin
2. **Dokümanları Kontrol Edin:**
   - `README.md`
   - `DEMO_CHECKLIST.md`
   - `REQUIREMENTS_CHECKLIST.md`

---

## ✅ Kurulum Tamamlandı!

Artık Campus Second-Hand Marketplace'i kullanmaya başlayabilirsiniz! 🎉

**Sıradaki Adımlar:**
1. `DEMO_CHECKLIST.md` dosyasını okuyun (demo senaryosu)
2. Farklı rollerle (Student, Moderator, Admin) giriş yapıp testi yapın
3. Tüm özellikleri keşfedin

**İyi Eğlenceler!** 🚀

---

**Son Güncelleme:** 17 Aralık 2024  
**Versiyon:** 1.0.0  
**Hazırlayan:** Ümmügülsün Türkmen (230611056)

