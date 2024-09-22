<?php

require_once 'connectorise.php';
include_once "partials/head.php";

// Veritabanı bağlantısını içeri al
$pdo = connectDatabase(); // $pdo'yu buradan alın

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'] ?? null;

// Kullanıcı bilgilerini almak için fonksiyonu güncelle
$query = "SELECT name, surname FROM users WHERE user_id = :user_id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kullanıcı adı ve soyadını al
$name = $user['name'] ?? null; // Eğer kullanıcı yoksa null
$surname = $user['surname'] ?? null; // Eğer kullanıcı yoksa null

// Sepeti Cookie'den al
function getCartItems() {
    if (isset($_COOKIE['cart'])) {
        return json_decode($_COOKIE['cart'], true);
    }
    return [];
}

// Sepetten ürün sil
function removeFromCart($itemName) {
    $cartItems = getCartItems();
    foreach ($cartItems as $index => $item) {
        if ($item['name'] === $itemName) {
            unset($cartItems[$index]); // Ürünü sepetten kaldır
            break;
        }
    }
    setcookie('cart', json_encode($cartItems), time() + 3600, '/'); // Sepeti güncelle
}

// Sil butonuna basıldığında
if (isset($_POST['remove_item'])) {
    $itemName = $_POST['remove_item'];
    removeFromCart($itemName); // Ürünü sepetten sil
    header("Location: " . $_SERVER['PHP_SELF']); // Sayfayı yenile
    exit;
}

// Sepet içeriğini al
$cartItems = getCartItems();

// Kullanıcı bilgilerini al
$user_name = $_SESSION['user_name'] ?? null; // Kullanıcı adı (giriş yapmışsa)

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media (max-width: 768px) {
            table {
                display: block;
                width: 100%;
            }
            thead {
                display: none; /* Başlıkları gizle */
            }
            tbody{
                width: 100%;
                display: table;
                table-layout: fixed;
            }
            tr {
                display: flex;
                flex-direction: column;
                border: 1px solid #ddd;
                margin-bottom: 1rem; /* Gruplar arasında boşluk */
            }
            td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem;
                border-top: 1px solid #ddd; /* Üstteki sınırı ekle */
            }
            td:last-child {
                border-bottom: 1px solid #ddd; /* Alt sınırı ekle */
            }
            td::before {
                content: attr(data-label); /* Hücre başlıklarını ekle */
                font-weight: bold;
                margin-right: 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 pt-4">

<div class="container mx-auto">
    <div class="md:flex justify-between items-center mb-3">

        <div class="w-32">
            <a href="app" class="bg-gray-700 text-white py-2 px-4 rounded inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H3m0 0l4 4m-4-4l4-4" />
                </svg>
                Geri
            </a>
        </div>

        <h1 class="text-3xl font-bold">Sepetinizdeki Ürünler</h1>

        <div class="flex justify-between items-center">
            <span class="text-lg">
                <?php 
                    if (!empty($name) && !empty($surname)) {
                        echo "Hoşgeldin " . htmlspecialchars($name);
                    } else {
                        echo "Hoşgeldin misafir"; 
                    } 
                ?>
            </span>
            <a href="<?php echo !empty($name) && !empty($surname) ? 'profile' : 'login'; ?>" class="bg-yellow-200 px-3 py-1 font-semibold rounded-md ml-3">
                <?php echo !empty($name) && !empty($surname) ? 'Profil' : 'Giriş'; ?>
            </a>
        </div>

    </div>

    <?php if (!empty($cartItems)): ?>

       <table class="min-w-full overflow-x-auto">

            <thead>
                <tr>
                    <th class="py-2 px-4 text-left bg-gray-50 border-t-2 border-b-2">Ürün Adı</th>
                    <th class="py-2 px-4 text-left bg-gray-50 border-t-2 border-b-2">Fiyat</th>
                    <th class="py-2 px-4 text-left bg-gray-50 border-t-2 border-b-2">Miktar</th>
                    <th class="py-2 px-4 text-left bg-gray-50 border-t-2 border-b-2">Aksiyon</th>
                </tr>
            </thead>

            <tbody>
                <?php $totalPrice = 0; ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td data-label="Ürün Adı" class="py-2 px-4 bg-white"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td data-label="Fiyat" class="py-2 px-4 bg-white" id="price-<?php echo htmlspecialchars($item['name']); ?>">₺<?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                        <td data-label="Miktar" class="py-2 px-4 bg-white">
                            <div class="flex items-center">
                                <button onclick="changeQuantity('<?php echo htmlspecialchars($item['name']); ?>', -1)" class="bg-gray-300 text-gray-700 px-3 py-1 rounded-l hover:bg-gray-400">-</button>

                                <input type="number" value="1" min="1" id="quantity-<?php echo htmlspecialchars($item['name']); ?>" data-unit-price="<?php echo $item['price']; ?>" onchange="updatePrice('<?php echo htmlspecialchars($item['name']); ?>')" class="w-16 py-1 text-center border border-gray-300" />

                                <button onclick="changeQuantity('<?php echo htmlspecialchars($item['name']); ?>', 1)" class="bg-gray-300 text-gray-700 px-3 py-1 rounded-r hover:bg-gray-400">+</button>
                            </div>
                        </td>
                        <td data-label="Aksiyon" class="py-2 px-4 bg-white">
                            <form method="POST" action="">
                                <button type="submit" name="remove_item" value="<?php echo htmlspecialchars($item['name']); ?>" class="bg-red-500 text-white px-2 py-1 rounded">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <?php $totalPrice += $item['price']; ?>
                <?php endforeach; ?>
                <tr>
                    <td class="text-lg font-bold py-4 px-4 bg-green-100">Toplam</td>
                    <td colspan="3" class="text-lg font-bold py-4 px-4 bg-green-100" id="total-price">₺<?php echo number_format($totalPrice, 2, ',', '.'); ?></td>
                </tr>
            </tbody>

        </table>

        <button id="checkout" class="bg-green-500 text-white font-medium p-2 rounded w-full mt-4">Ödeme Yap</button>

    <?php else: ?>
        <p>Sepetiniz boş.</p>
    <?php endif; ?>
</div>

<script>
    function changeQuantity(itemName, change) {
        const quantityInput = document.getElementById(`quantity-${itemName}`);
        let currentQuantity = parseInt(quantityInput.value);
        currentQuantity += change;
        if (currentQuantity < 1) currentQuantity = 1; // Miktar en az 1 olmalı
        quantityInput.value = currentQuantity;

        // Fiyatı güncelle
        updatePrice(itemName);
    }

    function updatePrice(itemName) {
        const quantityInput = document.getElementById(`quantity-${itemName}`);
        const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
        const quantity = parseInt(quantityInput.value);
        const price = unitPrice * quantity;
        
        document.getElementById(`price-${itemName}`).innerText = '₺' + price.toFixed(2).replace('.', ',');
        updateTotalPrice();
    }

    function updateTotalPrice() {
        const rows = document.querySelectorAll('tbody tr');
        let total = 0;
        rows.forEach(row => {
            const priceText = row.querySelector('td[id^="price-"]');
            if (priceText) {
                const priceValue = parseFloat(priceText.innerText.replace('₺', '').replace('.', '').replace(',', '.'));
                total += priceValue;
            }
        });

        // Toplam fiyatı göster
        document.getElementById('total-price').innerHTML = '₺' + total.toFixed(2).replace('.', ',');
    }

    document.getElementById('checkout').addEventListener('click', () => {
        window.location.href = '/login.php';
    });
</script>

</body>
</html>
