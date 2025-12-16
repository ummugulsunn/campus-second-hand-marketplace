<?php
/**
 * Admin Kullanıcısı Oluşturma Scripti
 * 
 * Bu script otomatik olarak admin kullanıcısı oluşturur.
 * Sadece bir kez çalıştırılmalıdır.
 * 
 * Kullanım: http://localhost/second-hand-market-place/create-admin.php
 */

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$adminEmail = isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)
    ? $_GET['email']
    : 'admin@campus.local';
$adminPassword = 'admin123'; // Varsayılan şifre
$adminName = 'Site Admin';

try {
    // 1. Admin rolünün ID'sini bul
    $roleSql = <<<SQL
    SELECT RoleID, RoleName
    FROM Role
    WHERE RoleName = 'Admin'
    LIMIT 1;
    SQL;
    
    $roleStmt = $pdo->query($roleSql);
    $role = $roleStmt->fetch();
    
    if (!$role) {
        die('❌ HATA: Admin rolü bulunamadı! Veritabanını kontrol et.');
    }
    
    $adminRoleId = (int)$role['RoleID'];
    
    // 2. Bu email ile kullanıcı var mı kontrol et
    $checkSql = <<<SQL
    SELECT UserID, Email
    FROM User
    WHERE Email = :email
    LIMIT 1;
    SQL;
    
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindValue(':email', $adminEmail, PDO::PARAM_STR);
    $checkStmt->execute();
    $existingUser = $checkStmt->fetch();
    
    // Her durumda şifreyi admin123'e sıfırla ve rolü Admin yap
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    if ($existingUser) {
        $updateSql = <<<SQL
        UPDATE User
        SET RoleID = :role_id,
            Password = :password
        WHERE UserID = :user_id;
        SQL;
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindValue(':role_id', $adminRoleId, PDO::PARAM_INT);
        $updateStmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $updateStmt->bindValue(':user_id', $existingUser['UserID'], PDO::PARAM_INT);
        $updateStmt->execute();
        
        echo "✅ Admin kullanıcısı güncellendi (rol + şifre sıfırlandı)!<br><br>";
    } else {
        $insertSql = <<<SQL
        INSERT INTO User (Name, Email, Password, Phone, RoleID)
        VALUES (:name, :email, :password, NULL, :role_id);
        SQL;
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindValue(':name', $adminName, PDO::PARAM_STR);
        $insertStmt->bindValue(':email', $adminEmail, PDO::PARAM_STR);
        $insertStmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $insertStmt->bindValue(':role_id', $adminRoleId, PDO::PARAM_INT);
        $insertStmt->execute();
        
        echo "✅ Admin kullanıcısı oluşturuldu!<br><br>";
    }
    
    // 3. Başarı mesajı ve giriş bilgileri
    echo "<h2>🎉 Admin Kullanıcısı Hazır!</h2>";
    echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Giriş Bilgileri:</h3>";
    echo "<p><strong>Email:</strong> <code>{$adminEmail}</code></p>";
    echo "<p><strong>Şifre:</strong> <code>{$adminPassword}</code></p>";
    echo "<p><small>Farklı bir email için URL'e <code>?email=ornek@domain.com</code> ekleyebilirsin.</small></p>";
    echo "</div>";
    
    echo "<p><a href='/second-hand-market-place/pages/login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔐 Giriş Sayfasına Git</a></p>";
    
    echo "<hr>";
    echo "<p><small>⚠️ Bu scripti tekrar çalıştırmaya gerek yok. Admin kullanıcısı hazır!</small></p>";
    
} catch (PDOException $e) {
    echo "❌ HATA: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo "<br><br>";
    echo "<p>Veritabanı bağlantısını kontrol et:</p>";
    echo "<ul>";
    echo "<li>AMPPS'te MySQL çalışıyor mu?</li>";
    echo "<li>Veritabanı import edildi mi? (<code>campus_marketplace</code>)</li>";
    echo "<li><code>config/db.php</code> dosyası doğru mu?</li>";
    echo "</ul>";
}

