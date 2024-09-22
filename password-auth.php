<?php
include_once "partials/head.php";

// password-auth.php
require_once 'functions.php';

// Eğer oturum başlatılmamışsa başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hata mesajlarını saklamak için değişkenleri tanımla
$passwordMismatchError = '';
$passwordLengthError = '';
$resetKeyError = '';
$successMessage = '';

// Parola değiştirme formu gönderildiyse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Diğer gerekli kontrolleri burada yapabilirsiniz.
    $newPassword = $_POST["newPassword"];
    $confirmPassword = $_POST["confirmPassword"];
    $resetKey = $_POST["resetKey"];

    // Yeni şifre ve doğrulama şifresi kontrolü
    if ($newPassword !== $confirmPassword) {
        $passwordMismatchError = "Passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $passwordLengthError = "Password must be at least 8 characters.";
    } else {
        // Reset key'i veritabanından kontrol et
        $query = "SELECT user_id FROM users WHERE reset_key = :reset_key";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':reset_key', $resetKey, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Yeni şifreyi hash'le
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Veritabanında şifreyi güncelle
            $updateSql = "UPDATE users SET password = :password, reset_key = NULL WHERE user_id = :user_id AND reset_key IS NOT NULL";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);

            if ($updateStmt->execute()) {
                // Parola değiştirme başarılı, mesajı ayarla
                $successMessage = 'Password updated successfully.';
                // Oturumu sıfırla
                session_unset();
                session_destroy();
            } else {
                $passwordUpdateError = 'An error occurred while updating the password.';
            }
        } else {
            // Reset key geçersizse, sadece bu hatayı göster
            $resetKeyError = 'Invalid reset key.';
            $passwordMismatchError = '';
            $passwordLengthError = '';
        }
    }
}
?>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-2">Password Recovery</h3>
                                </div>

                                <div class="card-body">
                                    <div class="small mb-3 text-muted">Enter your email address and we will send you a link to reset your password.</div>

                                    <?php if ($passwordMismatchError): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo $passwordMismatchError; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($passwordLengthError): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo $passwordLengthError; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resetKeyError): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo $resetKeyError; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($successMessage): ?>
                                        <div class="alert alert-success" role="alert">
                                            <?php echo $successMessage; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="password-auth" method="post">
                                        <div class="form-group">
                                            <label for="newPassword">New Password</label>
                                            <input type="password" name="newPassword" id="newPassword" class="form-control" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="confirmPassword">Confirm Password</label>
                                            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" required>
                                        </div>
                                        <input type="hidden" name="resetKey" value="<?php echo isset($_GET['key']) ? htmlspecialchars($_GET['key']) : ''; ?>">
                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                    </form>
                                </div>

                                <div class="card-footer text-center py-3">
                                    <div class="small">
                                        <a href="login">Go to login</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
