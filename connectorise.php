<?php
    function connectDatabase() {
        $dsn = 'mysql:host=localhost;dbname=products;charset=utf8mb4';
        $username = 'root';
        $password = ''; // Üretim ortamında güçlü bir şifre kullanın
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            return $pdo;
        } catch (PDOException $e) {
            die("Veritabanı hatası: " . $e->getMessage());
        }
    }
?>
