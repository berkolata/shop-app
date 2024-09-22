<?php
session_start();
require_once 'connectorise.php';

// PDO bağlantısını al
$pdo = connectDatabase();

try {
    // Tek bir ürün almak için SQL sorgusu
    $sql = "SELECT p.id, p.name, p.category, p.price, p.slug, f.feature, i.image_url
            FROM products p
            LEFT JOIN product_features f ON p.id = f.product_id
            LEFT JOIN product_images i ON p.id = i.product_id
            ORDER BY RAND() LIMIT 1"; // Rastgele bir ürün al

    // Sorguyu çalıştır
    $stmt = $pdo->query($sql);

    // Sonuçları al
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $product = [
            'id' => $row['id'],
            'name' => $row['name'],
            'category' => $row['category'],
            'price' => $row['price'],
            'slug' => $row['slug'],
            'features' => [],
            'images' => [],
        ];

        // Özellikleri ve resimleri eklemek için yeni bir sorgu
        $featuresSql = "SELECT feature FROM product_features WHERE product_id = :product_id";
        $featuresStmt = $pdo->prepare($featuresSql);
        $featuresStmt->bindParam(':product_id', $product['id'], PDO::PARAM_INT);
        $featuresStmt->execute();
        while ($featureRow = $featuresStmt->fetch(PDO::FETCH_ASSOC)) {
            $product['features'][] = $featureRow['feature'];
        }

        $imagesSql = "SELECT image_url FROM product_images WHERE product_id = :product_id";
        $imagesStmt = $pdo->prepare($imagesSql);
        $imagesStmt->bindParam(':product_id', $product['id'], PDO::PARAM_INT);
        $imagesStmt->execute();
        while ($imageRow = $imagesStmt->fetch(PDO::FETCH_ASSOC)) {
            $product['images'][] = $imageRow['image_url'];
        }

        echo json_encode($product);
    } else {
        echo json_encode(null);
    }

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>
