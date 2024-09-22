<?php
// index

session_start();

// Kullanıcı giriş yapmış mı kontrol et
if(isset($_SESSION['user_id'])) {
    // Kullanıcı giriş yapmışsa app sayfasına yönlendir
    header("Location: app");
    exit();
} else {
    // Kullanıcı giriş yapmamışsa login sayfasına yönlendir
    header("Location: login");
    exit();
}
?>