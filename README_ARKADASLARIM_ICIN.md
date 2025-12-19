# 🎓 Campus Marketplace - Ekip Kurulum Paketi

**Proje Sahibi:** Ümmügülsün Türkmen (230611056)  
**Versiyon:** 1.0.0  
**Tarih:** 17 Aralık 2024

Merhaba! 👋 Bu ZIP dosyası, **Campus Second-Hand Marketplace** projesinin tam kurulum paketini içeriyor.

---

## 📦 BU ZIP'TE NELER VAR?

### ✅ Dahil Olan Her Şey:

1. **💻 Tüm Kaynak Kodlar**
   - ✅ config/ (Ayar dosyaları)
   - ✅ includes/ (Header, Footer, Fonksiyonlar)
   - ✅ pages/ (Tüm sayfalar: admin, moderator, student)
   - ✅ assets/ (CSS dosyaları)
   - ✅ index.php (Ana sayfa)
   - ✅ logout.php
   - ✅ .htaccess (URL rewriting)

2. **🗄️ Database Dosyaları**
   - ✅ projectdb_export.sql (TAM database + tüm veriler)
   - ✅ stored_procedures.sql (15 stored procedure)
   - ✅ triggers.sql (5 trigger)

3. **📚 Dokümantasyon (12 Dosya)**
   - ✅ **QUICK_START.md** ⚡ - 5 DAKIKADA KURULUM! (BURADAN BAŞLA!)
   - ✅ INSTALLATION_GUIDE.md - Detaylı kurulum rehberi
   - ✅ NETWORK_SETUP_GUIDE.md - Birlikte simultane çalışma
   - ✅ DEMO_CHECKLIST.md - Demo senaryosu (10-15 dk)
   - ✅ README.md - Proje tanıtımı
   - ✅ Ve daha fazlası...

4. **📄 Proje Raporu**
   - ✅ CSE301 Report (PDF)

---

## ⚡ HIZLI BAŞLANGIÇ (5 DAKİKA!)

### Adım 1: ZIP'i Çıkart
Bu dosyayı istediğin yere çıkart (örn: Desktop)

### Adım 2: QUICK_START.md Aç
```
QUICK_START.md dosyasını aç ve takip et!
```

### Adım 3: 5 Adımı Takip Et
1. ✅ AMPPS/XAMPP başlat
2. ✅ Projeyi `www/` klasörüne kopyala
3. ✅ Database oluştur (`campus_marketplace`)
4. ✅ SQL'i import et (`projectdb_export.sql`)
5. ✅ Siteyi aç: `http://localhost/campus-marketplace/`

### Adım 4: Login Yap ve Test Et
```
Email: admin@istun.edu.tr
Password: password
```

**Bu kadar! ✅**

---

## 🌐 BİRLİKTE ÇALIŞMAK İSTİYORSAN

### Aynı WiFi'de Misiniz?
📖 **NETWORK_SETUP_GUIDE.md** dosyasını aç!
- LAN setup (önerilir)
- Herkes aynı database'i kullanır
- Gerçek simultane çalışma!

### Farklı Yerlerde Misiniz?
- Herkes kendi lokal'inde kurulum yapar
- Test/pratik için ideal

---

## 📋 KURULUM ÖNCESİ KONTROL

### Bilgisayarında Bunlar Var mı?

- [ ] **AMPPS veya XAMPP veya MAMP** (Birini kur)
- [ ] **10 dakika zamanın**
- [ ] **İnternet bağlantısı** (sadece kurulum için)

Yoksa önce bunları indir:
- **AMPPS:** http://ampps.com/downloads/
- **XAMPP:** https://www.apachefriends.org/download.html

---

## 🎯 HANGİ ROLÜ SEÇMELİYİM?

### Rol Önerileri (4 Kişi İçin):

| Kişi | Rol | Test Kullanıcısı | E-posta |
|------|-----|-----------------|---------|
| **Ümmügülsün** | Admin | Admin | `admin@istun.edu.tr` |
| **Arkadaş 1** | Moderator | Ayşe Kara | `ayse.kara@istun.edu.tr` |
| **Arkadaş 2** | Student (Seller) | Ahmet Yılmaz | `ahmet.yilmaz@istun.edu.tr` |
| **Arkadaş 3** | Student (Buyer) | Elif Öztürk | `elif.ozturk@istun.edu.tr` |

**Hepsinin şifresi:** `password`

---

## 🎬 DEMO HAZIRLIĞI

### Demo İçin:
1. **DEMO_CHECKLIST.md** dosyasını aç
2. 10-15 dakikalık senaryoyu oku
3. Rolünü belirle
4. Test et!

### Demo Günü:
- Herkes kendi rolü ile login olur
- Senaryo adım adım uygulanır
- Triggers ve notifications gösterilir
- Başarı! 🎉

---

## 🆘 SORUN MU YAŞIYORSUN?

### Yaygın Sorunlar:

**"Database connection failed"**
- MySQL çalışıyor mu? (Control Panel kontrol et)
- Database adı `campus_marketplace` mi?

**"404 Not Found"**
- Klasör adı doğru mu? `campus-marketplace`
- URL: `http://localhost/campus-marketplace/`

**"Import failed"**
- phpMyAdmin timeout? → SQL dosyasını terminal'den import et:
  ```bash
  mysql -u root campus_marketplace < projectdb_export.sql
  ```

### Detaylı Yardım:
📖 **INSTALLATION_GUIDE.md** → "Sorun Giderme" bölümü

---

## 📞 DESTEK

### Sorun Çözemediysen:
1. 📖 İlgili `.md` dosyasını oku
2. 🔍 INSTALLATION_GUIDE.md → "Sorun Giderme" kısmına bak
3. 💬 Bana yaz!

### GitHub:
```
https://github.com/ummugulsunn/campus-second-hand-marketplace
```

---

## 📊 PROJE HAKKıNDA

### Özellikler:
- ✅ 3 Kullanıcı Rolü (Student, Moderator, Admin)
- ✅ 11 Database Tablosu
- ✅ 15 Stored Procedure (7+ JOIN sorgusu)
- ✅ 5 Trigger (Auto-notifications)
- ✅ %93 Veri Trafiği Optimizasyonu
- ✅ Modern UI/UX (Bootstrap 5)
- ✅ Güvenli (Prepared Statements, Password Hashing)

### Fonksiyonlar:
- 📝 Listing oluşturma ve yönetimi
- 💰 Bidding sistemi
- 💬 Mesajlaşma
- ⭐ Review sistemi
- 🔔 Real-time notifications
- 📊 Admin dashboard
- 👮 Moderator approval system

---

## ✅ KURULUM TAMAMLANDIKTAN SONRA

1. **Test et!** Tüm özellikleri dene
2. **Demo hazırla!** DEMO_CHECKLIST.md oku
3. **Ekip ile çalış!** NETWORK_SETUP_GUIDE.md oku (opsiyonel)
4. **Eğlen!** 🎉

---

## 🎉 SON SÖZ

Bu proje, CSE301 Database Management dersi için özenle hazırlandı. Tüm gereksinimler %100 karşılandı ve production-ready durumda!

**İyi eğlenceler ve başarılar!** 🚀🎓

---

## 📅 ÖNEMLİ TARİHLER

- **Teslim:** 4 Ocak 2026 Pazar 23:59
- **Demo:** TBA (Sonra bildirilecek)

---

**Hazırlayan:** Ümmügülsün Türkmen  
**Öğrenci No:** 230611056  
**E-posta:** [GitHub'dan ulaşabilirsiniz]  
**Tarih:** 17 Aralık 2024  
**Versiyon:** 1.0.0 - Production Ready

---

## 🎯 ÖZETİN ÖZETİ

1. ⚡ **QUICK_START.md** aç → 5 dakikada kur
2. 🌐 **NETWORK_SETUP_GUIDE.md** aç → Birlikte çalış (opsiyonel)
3. 🎬 **DEMO_CHECKLIST.md** aç → Demo hazırla
4. 🎉 **Başarı!**

**Hadi başlayalım!** 💪


