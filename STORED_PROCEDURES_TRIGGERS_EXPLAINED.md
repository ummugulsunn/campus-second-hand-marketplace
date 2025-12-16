# STORED PROCEDURES VE TRIGGERS - PRATİK KULLANIM

## STORED PROCEDURES (Saklı Yordamlar)

### AMAÇ: Database'de Hazır Fonksiyonlar

**Basit Açıklama:** 
Stored procedure'lar, sık kullanılan SQL sorgularını database'de hazır tutmaktır. PHP'den tek satırla çağırırsın, database tüm işi yapar.

## SİTEMİZDEKİ KULLANIM ÖRNEKLERİ

### 1. sp_GetActiveListingsWithDetails - Ana Sayfa Listings

**ÖNCE (Stored Procedure OLMADAN):**
- PHP'de 4 ayrı sorgu yapıyordun
- Her listing için 4 sorgu = 20 listing × 4 = 80 sorgu!

**SONRA (Stored Procedure İLE):**
- Tek satır, hepsini bir arada getiriyor
- Sadece 1 sorgu!
- 80 sorgu → 1 sorgu (99% azalma!)

### 2. trg_AfterBidInsert - Bid Yerleştirildiğinde Otomatik Bildirim

**ÖNCE:**
- Bid'i ekle (1 sorgu)
- Seller'ı bul (1 sorgu)
- Notification oluştur (1 sorgu)
- Toplam: 3 sorgu

**SONRA:**
- Sadece bid'i ekle (1 sorgu)
- Trigger otomatik notification oluşturuyor
- Toplam: 1 sorgu (67% azalma!)

### 3. trg_AfterListingStatusUpdate - Ürün Satıldığında Tüm Bidder'lara Bildirim

**ÖNCE:**
- Listing'i güncelle (1 sorgu)
- Tüm bidder'ları bul (1 sorgu)
- Her birine notification gönder (10 sorgu)
- Toplam: 12 sorgu

**SONRA:**
- Sadece listing'i güncelle (1 sorgu)
- Trigger otomatik olarak 10 kişiye notification gönderiyor
- Toplam: 1 sorgu (92% azalma!)

## ÖZET

**Stored Procedures:**
- Hız: Çok daha hızlı
- Güvenlik: SQL injection riski daha düşük
- Temiz Kod: PHP'de daha az SQL kodu

**Triggers:**
- Otomasyon: Manuel işlem gerekmez
- Tutarlılık: Her zaman çalışır
- Performans: Daha az sorgu
