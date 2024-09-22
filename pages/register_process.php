<?php
include 'connectorise.php';

// Hata raporlarını göster
error_reporting(E_ALL);
ini_set('display_errors', 1);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Formdan gelen verileri al
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $surname = isset($_POST["surname"]) ? $_POST["surname"] : "";
        $email = isset($_POST["email"]) ? $_POST["email"] : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $passwordConfirm = isset($_POST["passwordConfirm"]) ? $_POST["passwordConfirm"] : "";

        // Gerekli doğrulamaları yap, şifreleri kontrol et
        if ($password != $passwordConfirm) {
            // Hata durumunda kullanıcıyı kayıt sayfasına geri yönlendir
            header("Location: register?error=password_mismatch");
            exit();
        }

        if (strlen($password) < 8) {
            // Hata durumunda kullanıcıyı kayıt sayfasına geri yönlendir
            header("Location: register?error=weak_password");
            exit();
        }

        // E-posta format kontrolü yap
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: register?error=invalid_email_format");
            exit();
        }

        // E-posta benzersiz olmalı
        $query = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $emailCount = $stmt->fetchColumn();

        if ($emailCount > 0) {
            header("Location: register?error=email_already_exists");
            exit();
        }

        // Kullanıcıyı veritabanına ekle (şifre hashleme işlemi burada yapılmalıdır)
        // Önce kullanıcının şifresini hash'le
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Şimdi veritabanına ekleme işlemi yapabilirsiniz
            $query = "INSERT INTO users (name, surname, email, password) VALUES (:name, :surname, :email, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->execute();

            // Diğer bindParam ve execute işlemlerini gerçekleştir
            // $stmt->bindParam(':diğer_param', $diğer_değer, PDO::PARAM_TIPI);
            // ...

            // İşlem başarılıysa oturumu başlat ve kullanıcıyı giriş sayfasına yönlendir
            session_start();
            $_SESSION["user_id"] = $pdo->lastInsertId(); // Yeni eklenen kullanıcının ID'sini alabilirsiniz
            header("Location: register?success=registration_successful");
            exit();
        } catch (PDOException $e) {
            // Hata durumunda kullanıcıyı kayıt sayfasına geri yönlendir
            header("Location: register?error=database_error");
            exit();
        }
    }
?>