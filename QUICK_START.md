# ⚡ Hızlı Başlangıç Rehberi

**Campus Second-Hand Marketplace** - 5 Dakikada Kurulum! 🚀

---

## 🎯 Ne Yapacağız?

Bu rehber, projeyi 5 dakikada bilgisayarınıza kurup çalıştırmanızı sağlayacak.

---

## 📋 Gereksinimler

- ✅ AMPPS, XAMPP veya MAMP (hangisi varsa)
- ✅ 10 dakika zamanınız
- ✅ İnternet bağlantısı (sadece kurulum için)

---

## 🚀 5 Adımda Kurulum

### 1️⃣ AMPPS/XAMPP'i Başlatın

**AMPPS Control Panel'i açın ve:**
- ✅ **Apache** → Start (yeşil olmalı)
- ✅ **MySQL** → Start (yeşil olmalı)

### 2️⃣ Projeyi İndirin

**GitHub'dan indirin:**
```
https://github.com/ummugulsunn/campus-second-hand-marketplace
```

**"Code" → "Download ZIP" → ZIP'i çıkartın**

**Klasörü şuraya kopyalayın:**
- **Windows:** `C:\Program Files\Ampps\www\`
- **Mac:** `/Applications/AMPPS/www/`

**Sonuç:**
```
www/campus-second-hand-marketplace/
├── config/
├── includes/
├── pages/
├── index.php
└── projectdb_export.sql  ← Bu dosyayı kullanacağız!
```

### 3️⃣ Database Oluşturun

**1. phpMyAdmin'i açın:**
```
http://localhost/phpmyadmin/
```

**2. Sol tarafta "New" (Yeni) tıklayın**

**3. Database adı:**
```
campus_marketplace
```

**4. Collation:**
```
utf8mb4_unicode_ci
```

**5. "Create" tıklayın** ✅

### 4️⃣ Database'i Import Edin

**1. `campus_marketplace` database'ine tıklayın**

**2. Üstte "Import" (İçe Aktar) sekmesine tıklayın**

**3. "Choose File" → `projectdb_export.sql` dosyasını seçin**

**4. "Go" (Çalıştır) tıklayın**

**5. ✅ "Import successfully finished" mesajını görmelisiniz!**

### 5️⃣ Siteyi Açın ve Test Edin!

**Tarayıcınızda açın:**
```
http://localhost/campus-second-hand-marketplace/
```

**Login yapın:**
- **Email:** `admin@istun.edu.tr`
- **Password:** `password`

**✅ Başarılı! Ana sayfayı görüyor musunuz?**

---

## 🎭 Test Kullanıcıları

Farklı roller için hazır hesaplar:

| Rol | Email | Şifre |
|-----|-------|-------|
| **Admin** | `admin@istun.edu.tr` | `password` |
| **Moderator** | `ayse.kara@istun.edu.tr` | `password` |
| **Student** | `ahmet.yilmaz@istun.edu.tr` | `password` |

---

## 🎯 İlk Adımlar

### Admin Olarak:
1. Sağ üstte **"Admin"** butonuna tıklayın
2. Dashboard'da istatistikleri görün
3. "Manage Categories" → Yeni kategori ekleyin
4. "Manage Users" → Kullanıcıları görün

### Student Olarak:
1. **"+ Listing"** butonuna tıklayın
2. Yeni bir ürün ilanı oluşturun
3. "Listings" → Başka ürünlere teklif verin
4. "Messages" → Satıcılarla mesajlaşın

### Moderator Olarak:
1. **"Manage"** butonuna tıklayın
2. Pending (bekleyen) ilanları görün
3. Status'ü **Active** yapın (onayla)
4. "Complaints" → Şikayetleri yönetin

---

## 🌐 Arkadaşlarınızla Birlikte Çalışma

**Aynı WiFi'de misiniz?** → [NETWORK_SETUP_GUIDE.md](NETWORK_SETUP_GUIDE.md) okuyun!

**Farklı yerlerde misiniz?** → Herkes kendi lokal'inde kurulum yapsın.

---

## 🐛 Sorun mu Yaşıyorsunuz?

### "Database connection failed"
- ✅ MySQL çalışıyor mu? (Control Panel'de kontrol edin)
- ✅ Database adı `campus_marketplace` mi?

### "404 Not Found"
- ✅ Klasör adı doğru mu? `campus-second-hand-marketplace`
- ✅ URL: `http://localhost/campus-second-hand-marketplace/`

### "Access Denied"
- ✅ `config/db.php` → şifre boş mu? (`'pass' => ''`)
- ✅ MAMP kullanıyorsanız şifre `root` olabilir

### Hala Sorun Var?
📖 **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Detaylı rehberi okuyun!

---

## 📚 Daha Fazla Bilgi

- 📖 **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Detaylı kurulum (tüm sorunlar için)
- 🌐 **[NETWORK_SETUP_GUIDE.md](NETWORK_SETUP_GUIDE.md)** - Simultane çalışma
- 🎬 **[DEMO_CHECKLIST.md](DEMO_CHECKLIST.md)** - Demo senaryosu (10-15 dk)
- 📊 **[FINAL_RELEASE_SUMMARY.md](FINAL_RELEASE_SUMMARY.md)** - Proje özeti

---

## ✅ Kurulum Tamamlandı!

**Artık sistemi kullanmaya başlayabilirsiniz!** 🎉

**Demo için hazırlık:**
1. Farklı rollerle login yapıp test edin
2. Tüm özellikleri deneyin
3. `DEMO_CHECKLIST.md` okuyun

**İyi Eğlenceler!** 🚀

---

**Sorularınız için:** [GitHub Issues](https://github.com/ummugulsunn/campus-second-hand-marketplace/issues)

**Proje Sahibi:** Ümmügülsün Türkmen (230611056)  
**Versiyon:** 1.0.0  
**Tarih:** 17 Aralık 2024

