<?php
    // logout.php

    session_start();

    // "Remember Me" çerezini sil
    if (isset($_COOKIE["remembered_user"])) {
        setcookie("remembered_user", "", time() - 3600); // Geçmiş bir tarihe ayarlayarak çerezi sil
    }

    // Oturumu sonlandır
    session_destroy();

    // Kullanıcıyı giriş sayfasına yönlendir
    header("Location: index");
    exit();
?>
