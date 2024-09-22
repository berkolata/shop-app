<?php
    include_once "partials/head.php";
    // password-reset-request.php
    require_once 'functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Formdan e-posta adresini al
        $email = $_POST['email'];
    
        // E-posta adresini kullanarak kullanıcıyı bul ve sıfırlama bağlantısı gönder
        sendPasswordResetEmail($email);
        exit;
    }
    
?>