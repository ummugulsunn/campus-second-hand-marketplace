# 🚀 AMPPS Kurulum Rehberi

## 📋 Adım Adım Kurulum

### 1️⃣ AMPPS'i Başlat
- **AMPPS** uygulamasını aç
- **Apache** ve **MySQL** servislerini **Start** et
- Yeşil ışık yanınca hazırsın ✅

### 2️⃣ Projeyi AMPPS'e Taşı
AMPPS'in `htdocs` klasörüne projeyi kopyala:

```bash
# Terminal'de çalıştır:
cp -r /Users/ummugulsun/second-hand-market-place /Applications/AMPPS/www/
```

**Veya manuel olarak:**
- Finder'da `/Applications/AMPPS/www/` klasörünü aç
- `second-hand-market-place` klasörünü buraya kopyala

### 3️⃣ Veritabanını Oluştur ve Import Et

#### Yöntem A: phpMyAdmin ile (Kolay)
1. Tarayıcıda aç: `http://localhost/phpmyadmin`
2. Sol tarafta **"New"** tıkla
3. Database name: **`campus_marketplace`**
4. Collation: **`utf8mb4_general_ci`**
5. **"Create"** tıkla
6. Üst menüden **"Import"** sekmesine git
7. **"Choose File"** → `projectdb.sql` dosyasını seç
8. **"Go"** tıkla

#### Yöntem B: Terminal ile
```bash
mysql -u root -p < /Applications/AMPPS/www/second-hand-market-place/projectdb.sql
```

### 4️⃣ Veritabanı Bağlantısını Kontrol Et

AMPPS'in default MySQL ayarları:
- **Host:** `localhost`
- **User:** `root`
- **Password:** *(boş)*

`config/db.php` dosyan zaten bu ayarlarla uyumlu! ✅

### 5️⃣ Projeyi Tarayıcıda Aç

```
http://localhost/second-hand-market-place/index.php
```

veya sadece:

```
http://localhost/second-hand-market-place/
```

---

## 👑 Admin Kullanıcısı Oluşturma

### Yöntem 1: Otomatik Script (Önerilen) 🎯

1. Tarayıcıda aç:
   ```
   http://localhost/second-hand-market-place/create-admin.php
   ```

2. Bu script otomatik olarak admin kullanıcısı oluşturacak.

3. **Admin Giriş Bilgileri:**
   - **Email:** `admin@campus.local`
   - **Şifre:** `admin123`

### Yöntem 2: Manuel (phpMyAdmin)

1. `http://localhost/phpmyadmin` → `campus_marketplace` → `Role` tablosuna bak
2. Admin'in `RoleID` değerini not et (genelde `3`)
3. `User` tablosuna git → **"Insert"** tıkla
4. Şu değerleri gir:
   - **Name:** `Site Admin`
   - **Email:** `admin@campus.local`
   - **Password:** `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` (bu `admin123` şifresinin hash'i)
   - **Phone:** *(boş bırak)*
   - **RoleID:** `3` (veya Admin'in RoleID'si)

5. **"Go"** tıkla

### Yöntem 3: Mevcut Kullanıcıyı Admin Yap

1. Uygulamadan normal bir kullanıcı kaydet (Register sayfasından)
2. `http://localhost/phpmyadmin` → `campus_marketplace` → `User` tablosuna git
3. Kendi email'ini bul
4. `RoleID` değerini **Admin'in RoleID'si** ile değiştir (genelde `3`)
5. **"Go"** tıkla

---

## ✅ Test Et

1. **Ana Sayfa:** `http://localhost/second-hand-market-place/`
2. **Login:** `http://localhost/second-hand-market-place/pages/login.php`
3. **Admin Dashboard:** `http://localhost/second-hand-market-place/pages/admin/dashboard.php`

---

## 🔧 Sorun Giderme

### Veritabanı bağlantı hatası?
- AMPPS'te MySQL'in çalıştığından emin ol (yeşil ışık)
- `config/db.php` dosyasında şifre boş olmalı (AMPPS default)

### Sayfa bulunamadı (404)?
- Projenin `/Applications/AMPPS/www/second-hand-market-place/` klasöründe olduğundan emin ol
- URL'de büyük/küçük harf duyarlılığına dikkat et

### Admin sayfasına erişemiyorum?
- Kullanıcının `RoleID` değerinin Admin olduğundan emin ol
- `Role` tablosunda Admin'in `RoleID` değerini kontrol et

---

## 📝 Notlar

- AMPPS default port: **80** (Apache) ve **3306** (MySQL)
- Proje klasörü: `/Applications/AMPPS/www/second-hand-market-place/`
- phpMyAdmin: `http://localhost/phpmyadmin`


