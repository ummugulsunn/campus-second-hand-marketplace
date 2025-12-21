# Campus Second-Hand Marketplace

**Version 1.0.0 - Production Ready** | **Final Release: 17 Aralık 2024**

A comprehensive second-hand marketplace platform designed exclusively for university students and staff. Built with Native PHP, MySQL, and Bootstrap 5.

> **Status:** Demo hazır! Tüm özellikler test edildi ve çalışıyor.  
> **Stats:** 15 Stored Procedures, 5 Triggers, %93 Optimizasyon  
> **Requirements:** 100% tamamlandı

## Project Overview

This platform allows users to:
- **List items** for sale (textbooks, electronics, furniture, dorm equipment)
- **Place bids** on items
- **Message sellers** directly
- **Leave reviews** based on actual transactions
- **Save items** to wishlist
- **Report issues** to moderators

## User Roles

### Student
- Create and manage listings
- Place bids on items
- Send/receive messages
- Leave reviews
- Save items to wishlist

### Moderator
- Review and manage complaints
- Moderate listings (approve/remove)
- Change listing statuses

### Admin
- Manage users and roles
- Manage categories (CRUD)
- View comprehensive dashboard statistics
- Access all moderator features

## Tech Stack

- **Backend:** Native PHP 8.0+
- **Database:** MySQL (PDO with Prepared Statements)
- **Frontend:** Bootstrap 5 (CDN)
- **Security:** 
  - SQL Injection protection (Prepared Statements)
  - XSS protection (Input sanitization)
  - Password hashing (bcrypt)
  - Role-based access control

## Project Structure

```
second-hand-market-place/
├── config/
│   └── db.php              # Database connection
├── includes/
│   ├── header.php          # Common header & navbar
│   ├── footer.php          # Common footer & scripts
│   └── functions.php       # Helper functions
├── assets/
│   └── css/
│       └── style.css       # Custom styles
├── pages/
│   ├── admin/              # Admin pages
│   ├── moderator/          # Moderator pages
│   └── [other pages]       # User pages
└── index.php              # Landing page
```

## Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd second-hand-market-place
   ```

2. **Import database**
   ```bash
   mysql -u your_username -p campus_marketplace < projectdb.sql
   ```

3. **Configure database**
   - Update `config/db.php` with your database credentials
   - Or set environment variables:
     ```bash
     export DB_HOST=localhost
     export DB_NAME=campus_marketplace
     export DB_USER=your_username
     export DB_PASS=your_password
     ```

4. **Start development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**
   - Open browser: `http://localhost:8000`
   - Register a new account (default role: Student)
   - Or use test accounts (see below)

## Test Accounts

### Student
- Email: `ahmet.yilmaz@istun.edu.tr`
- Password: `password`

### Moderator
- Email: `ayse.kara@istun.edu.tr`
- Password: `password`
- Note: Update RoleID to 2 in database

### Admin
- Create your own admin account or update existing user's RoleID to 3

## Features

### Core Features
- User authentication (Register/Login/Logout)
- Role-based access control
- Product listings (CRUD)
- Bidding system
- Messaging system
- Review system (interaction-based)
- Saved items (wishlist)
- Notifications
- Complaint system

### Advanced Features
- Advanced filtering & sorting
- Real-time form validation
- Auto-save drafts
- Interactive star rating
- Toast notifications
- Image placeholders
- Loading states
- Breadcrumb navigation
- Back to top button

## Database Schema

The database includes the following tables:
- `User` - User accounts
- `Role` - User roles (Student, Moderator, Admin)
- `Product_Listing` - Product listings
- `Category` - Product categories
- `Bid` - Bids on listings
- `Message` - Private messages
- `Review` - User reviews
- `Complaint_Report` - Complaint reports
- `Notification` - System notifications
- `Saved_Item` - Saved items (wishlist)

## Security Features

- **SQL Injection Protection:** All queries use PDO Prepared Statements
- **XSS Protection:** Input sanitization with `cleanInput()`
- **Password Security:** Bcrypt hashing
- **Session Management:** Secure session handling
- **Role-based Access:** `hasRole()` checks on protected pages

## Demo Checklist

See `DEMO_CHECKLIST.md` for detailed demo scenarios and testing procedures.

## UI/UX Features

- Modern, responsive design
- Smooth animations and transitions
- Empty states with helpful CTAs
- Loading states and skeletons
- Toast notifications
- Form validation with real-time feedback
- Interactive star rating
- Image placeholders

## License

This project is developed for academic purposes as part of a Database Management course.

## Contributors

- Ümmügülsün Türkmen (230611056)
- Group Members:
  - Ayşenur Otaran (220611034)
  - Büşra Demirel (220611029)
  - Şeyma Akın (220611012)

## Project Timeline

- **Report 1:** Database schema design
- **Final Submission:** January 4, 2026
- **Demo:** TBA

---

## Kurulum ve Setup Rehberleri

### Hızlı Başlangıç:
1. **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Detaylı kurulum adımları (AMPPS/XAMPP/MAMP)
2. **[NETWORK_SETUP_GUIDE.md](NETWORK_SETUP_GUIDE.md)** - Simultane çalışma için network setup
3. **[DEMO_CHECKLIST.md](DEMO_CHECKLIST.md)** - Demo senaryosu (10-15 dakika)

### Dokümantasyon:
- **[REQUIREMENTS_CHECKLIST.md](REQUIREMENTS_CHECKLIST.md)** - Proje gereksinimleri (100% tamamlandı)
- **[SQL_QUERIES_REPORT.md](SQL_QUERIES_REPORT.md)** - Kritik SQL sorguları (15 SP, 7+ JOIN)
- **[DATA_TRAFFIC_OPTIMIZATION.md](DATA_TRAFFIC_OPTIMIZATION.md)** - %93 optimizasyon raporu
- **[FINAL_RELEASE_SUMMARY.md](FINAL_RELEASE_SUMMARY.md)** - Final release notları

### Hızlı Test:
```bash
# 1. AMPPS/XAMPP başlatın
# 2. Database'i import edin (projectdb_export.sql)
# 3. Tarayıcıda açın:
http://localhost/campus-marketplace/

# 4. Test kullanıcısı ile login:
Email: admin@istun.edu.tr
Password: password
```

---

**Built with care for university students**

