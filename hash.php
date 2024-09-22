<?php
    $plainPassword = "password";
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    echo "Hashlenmiş Şifre: " . $hashedPassword;
?>