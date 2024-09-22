<?php
// Hata raporları aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantısını dahil et
require_once 'connectorise.php';
$pdo = connectDatabase();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Hata raporları aktif et
error_reporting(-1);

// PHPMailer dosyalarını dahil et
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// SMTP ayarlarını genel olarak tanımla
$smtpHost = 'mail.berkant.xyz';
$smtpUsername = 'test@berkant.xyz';
$smtpPassword = 's2Dk5An5B';
$smtpSecure = 'tls';
$smtpPort = 587;


    function loginUser($email, $password, $rememberMe = false) {
        global $pdo;

        // Formdan gelen verileri kontrol et
        if (empty($email) || empty($password)) {
            header("Location: login?error=empty_fields");
            exit();
        }

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
                    $_SESSION["user_id"] = $user['user_id'];

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

    function registerUser($name, $surname, $email, $password, $passwordConfirm) {
        global $pdo;
    
        // Gerekli doğrulamaları yap, şifreleri kontrol et
        if (empty($name) || empty($surname) || empty($email) || empty($password) || empty($passwordConfirm)) {
            header("Location: register?error=empty_fields");
            exit();
        }
    
        if ($password != $passwordConfirm) {
            header("Location: register?error=password_mismatch");
            exit();
        }
    
        if (strlen($password) < 8) {
            header("Location: register?error=weak_password");
            exit();
        }
    
        // E-posta format kontrolü yap
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

    function sendEmailWithPHPMailer($to, $subject, $message) {
        global $smtpHost, $smtpUsername, $smtpPassword, $smtpSecure, $smtpPort;

        $mail = new PHPMailer(true);

        try {
            // SMTP ayarları
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port = $smtpPort;

            // Gönderen bilgileri
            $mail->setFrom('test@berkant.xyz', 'Berkant Fidan Test');
            
            // Alıcı bilgileri
            $mail->addAddress($to);

            // E-posta içeriği
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // E-postayı gönder
            $mail->send();

            return true;
        } catch (Exception $e) {
            // Hata durumunda ekrana yazdır
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            return false;
        }
    }

    function sendPasswordResetEmail($email) {
        global $pdo;

        // E-posta adresine sahip kullanıcıyı bul
        $query = "SELECT user_id, name, email FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Kullanıcıyı bulduysak, sıfırlama anahtarı oluştur
            $resetKey = bin2hex(random_bytes(16));

            // Veritabanında sıfırlama anahtarını kaydet
            $updateSql = "UPDATE users SET reset_key = :reset_key WHERE user_id = :user_id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':reset_key', $resetKey, PDO::PARAM_STR);
            $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
            $updateStmt->execute();

            // E-posta gönderme işlemi (PHPMailer kullanarak)
            $to = $user['email'];
            $subject = "Password Reset";
            $message = "Hello " . $user['name'] . ",\n\nClick the following link to reset your password:\n\n";
            $message .= '<a href="http://app.dev/password-auth?key=' . $resetKey . '">Click here to reset your password</a>';

            if (sendEmailWithPHPMailer($to, $subject, $message)) {
                echo "";
                echo '<div class="container mt-5">
                <div class="alert alert-success" role="alert">
                    Password reset link has been sent to your email address.
                </div>
              </div>';
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to send the password reset email.</div>';
            }
        } else {
            echo '<div class="container mt-5"><div class="alert alert-danger alert-dismissible fade show" role="alert">No user found with this email address. <a href="password">Go back</a></div></div>';
        }
    }

    function showErrorMessage($error) {
        $errorMessage = "";
        switch ($error) {
            case "empty_fields":
                $errorMessage = "Username and password fields cannot be left blank!";
                break;
            case "wrong_password":
                $errorMessage = "Password is incorrect!";
                break;
            case "wrong_username":
                $errorMessage = "Invalid user info!";
                break;
            case "password_mismatch":
                $errorMessage = "Passwords do not match!";
                break;
            case "weak_password":
                $errorMessage = "Password must be at least 8 characters!";
                break;
            case "invalid_email_format":
                $errorMessage = "Invalid email format!";
                break;
            case "email_already_exists":
                $errorMessage = "This email address is already registered!";
                break;
            case "database_error":
                $errorMessage = "Database error, please try again!";
                break;
            default:
                $errorMessage = "An unknown error occurred!";
                break;
        }

        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> ' . $errorMessage . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }

    function displayRegistrationMessages() {
        if (isset($_GET["error"])) {
            $errorMessage = "";
            switch ($_GET["error"]) {
                case "password_mismatch":
                    $errorMessage = "Passwords do not match!";
                    break;
                case "weak_password":
                    $errorMessage = "Password must be at least 8 characters!";
                    break;
                case "invalid_email_format":
                    $errorMessage = "Invalid email format!";
                    break;
                case "email_already_exists":
                    $errorMessage = "This email address is already registered!";
                    break;
                case "database_error":
                    $errorMessage = "Database error, please try again!";
                    break;
                default:
                    $errorMessage = "Please fill the empty inputs.";
                    break;
            }
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ' . $errorMessage . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
        if (isset($_GET["success"]) && $_GET["success"] == "registration_successful") {
            echo '<div class="alert alert-success" role="alert" id="registrationSuccessMessage">Registration has been completed successfully. <a href="app">You can log in now!</a></div>';
        }
    }

    function showSuccessMessage($success) {
        if ($success == "registration_successful") {
            echo '<div class="alert alert-success" role="alert" id="registrationSuccessMessage">
                Registration has been completed successfully. <a href="app">You can log in now!</a>
            </div>';
        }
    }
    
    // Oturumu başlat
    session_start();
?>
