# 🚀 GitHub Repository Setup

## Adım 1: GitHub'da Yeni Repo Oluştur

1. GitHub'a git: https://github.com
2. Sağ üstteki **"+"** butonuna tıkla → **"New repository"**
3. Repository bilgileri:
   - **Repository name:** `campus-second-hand-marketplace`
   - **Description:** `A comprehensive second-hand marketplace platform for university students`
   - **Visibility:** Private (önerilir) veya Public
   - **Initialize:** ❌ README, .gitignore, license ekleme (biz zaten ekledik)
4. **"Create repository"** butonuna tıkla

## Adım 2: Local Repo'yu GitHub'a Bağla

Terminal'de şu komutları çalıştır:

```bash
cd /Users/ummugulsun/second-hand-market-place

# GitHub repo URL'ini ekle (YOUR_USERNAME'i kendi GitHub kullanıcı adınla değiştir)
git remote add origin https://github.com/YOUR_USERNAME/campus-second-hand-marketplace.git

# Branch'i main olarak değiştir (GitHub default)
git branch -M main

# İlk push
git push -u origin main
```

## Adım 3: GitHub Credentials

Eğer authentication sorunu yaşarsan:

### Option 1: Personal Access Token (Önerilir)
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. "Generate new token" → "Generate new token (classic)"
3. Note: "Campus Marketplace Repo"
4. Expiration: 90 days (veya istediğin süre)
5. Scopes: ✅ `repo` (tüm repo yetkileri)
6. "Generate token" → Token'ı kopyala
7. Push yaparken password yerine bu token'ı kullan

### Option 2: SSH Key
```bash
# SSH key oluştur (eğer yoksa)
ssh-keygen -t ed25519 -C "your_email@example.com"

# Public key'i GitHub'a ekle
cat ~/.ssh/id_ed25519.pub
# Bu çıktıyı GitHub → Settings → SSH and GPG keys → New SSH key
```

## Adım 4: Push Kontrolü

```bash
# Remote'ları kontrol et
git remote -v

# Son commit'i kontrol et
git log --oneline -1

# Push yap
git push -u origin main
```

## ✅ Başarılı Push Sonrası

GitHub repo sayfasında tüm dosyalarını göreceksin!

---

## 🔄 Gelecek Değişiklikler İçin

Her değişiklikten sonra:

```bash
# Değişiklikleri kontrol et
git status

# Değişiklikleri ekle
git add .

# Commit yap
git commit -m "Kısa açıklama: Ne değişti?"

# Push yap
git push
```

---

## 📝 Commit Mesajları İçin Öneriler

- `feat: Add new feature`
- `fix: Fix bug`
- `style: Update UI/UX`
- `refactor: Code improvement`
- `docs: Update documentation`
- `test: Add tests`

Örnek:
```bash
git commit -m "feat: Add image placeholders for listings"
git commit -m "fix: Resolve dropdown z-index issue"
git commit -m "style: Improve mobile responsiveness"
```

---

**Not:** Eğer GitHub repo URL'ini paylaşırsan, remote ekleme komutunu senin için hazırlayabilirim! 🚀

