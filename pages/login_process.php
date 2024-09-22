<?php
// Hata raporları aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// login_process.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kullanıcıdan gelen verileri kontrol et
    $email = isset($_POST["email"]) ? $_POST["email"] : null;
    $password = isset($_POST["password"]) ? $_POST["password"] : null;

    // Kullanıcı adı veya şifre alanları boşsa, login sayfasına geri yönlendir ve hata mesajını göster
    if (empty($email) || empty($password)) {
        header("Location: login?error=empty_fields");
        exit();
    }

    // connection dosyasını include et
    include 'connectorise.php';

    try {
        // Kullanıcı bilgilerini çek
        $query = "SELECT user_id, password FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Kullanıcı adı doğru, şifreyi kontrol et (şifrelerin hashlenmiş olması önerilir)
            if (password_verify($password, $user['password'])) {
                // Kullanıcı doğrulandı, oturumu başlat
                $_SESSION["user_id"] = $user['user_id']; // user_id'yi atıyoruz
        
                // Remember Me seçeneği işaretlendiyse çerez oluştur
                if ($rememberMe) {
                    setcookie("remembered_user", $username, time() + (30 * 24 * 60 * 60)); // 30 gün süreyle geçerli çerez
                }
        
                // Dashboard sayfasına yönlendir
                header("Location: app");
                exit();
            } else {
                // Şifre yanlış, login sayfasına geri yönlendir ve hata mesajını göster
                header("Location: login?error=wrong_password");
                exit();
            }
        } else {
            // Kullanıcı adı yanlış, login sayfasına geri yönlendir ve hata mesajını göster
            header("Location: login?error=wrong_username");
            exit();
        }
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        exit();
    }
}
?>