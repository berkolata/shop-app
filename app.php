<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'connectorise.php';
    include_once "partials/head.php";

    // Kullanıcı bilgilerini al
    $user_id = $_SESSION['user_id'] ?? null; // Eğer tanımlı değilse null döner

    // Veritabanı bağlantısını içeri al
    connectDatabase();

    $query = "SELECT name, surname FROM users WHERE user_id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kullanıcı adı ve soyadını al
    $name = $user['name'] ?? null; // Eğer kullanıcı yoksa null
    $surname = $user['surname'] ?? null; // Eğer kullanıcı yoksa null

    // PDO bağlantısını al
    $pdo = connectDatabase();

    try {
        // İlk ürünü almak için SQL sorgusu
        $sql = "SELECT p.id, p.name, p.category, p.price, p.slug, f.feature, i.image_url
                FROM products p
                LEFT JOIN product_features f ON p.id = f.product_id
                LEFT JOIN product_images i ON p.id = i.product_id
                ORDER BY RAND()"; // Rastgele ürünler al

        // Sorguyu çalıştır
        $stmt = $pdo->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Eğer ürün yoksa hata mesajı göster
        if (empty($products)) {
            echo "Ürün bulunamadı.";
            exit; // Daha fazla işlem yapma
        }

    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        exit; // Daha fazla işlem yapma
    }
?>

<body class="bg-gray-100 h-screen">

    <div class="flex flex-col md:flex-row h-full">

        <!-- Sol taraf (Carousel ve Zappla butonu) -->
        <div class="md:w-4/5 h-2/3 md:h-full w-full relative overflow-hidden">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="image1.jpg" alt="Image 1"></div>
                    <div class="swiper-slide"><img src="image2.jpg" alt="Image 2"></div>
                    <div class="swiper-slide"><img src="image3.jpg" alt="Image 3"></div>
                    <!-- Daha fazla slide ekleyebilirsiniz -->
                </div>

                <!-- Sayfalama -->
                <div class="swiper-pagination"></div>

                <!-- Önceki ve sonraki düğmeler -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <!-- Sağ taraf (Ürün Bilgileri) -->
        <div class="md:w-1/5 w-full h-1/3 md:h-full flex flex-col justify-between bg-white p-4">
            <div class="flex justify-between items-center">
                <span>
                    <?php 
                        if (!empty($name) && !empty($surname)) {
                            echo "Hoşgeldin " . htmlspecialchars($name);
                        } else {
                            echo "Hoşgeldin misafir"; 
                        } 
                    ?>
                </span>
                <a href="<?php echo !empty($name) && !empty($surname) ? 'logout' : 'login'; ?>" class="bg-yellow-200 px-3 py-1 font-semibold rounded-md">
                    <?php echo !empty($name) && !empty($surname) ? 'Çıkış' : 'Giriş'; ?>
                </a>
            </div>

            <div>
                <h2 id="productName" class="text-2xl font-bold mb-2"></h2>
                <p id="category" class="text-lg mb-2"></p>
                <p id="price" class="text-lg mb-2"></p>
                <ul id="features" class="list-disc pl-5 mb-4"></ul>
            </div>
            
             <!-- Sepet alanı -->
            <div id="cart">
                <h3 class="text-xl font-bold mb-2 flex justify-between items-center">
                    Sepet 
                    <button id="toggleCart" class="focus:outline-none">
                        <svg id="cartChevron" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </h3>
                <ul id="cartItems" class="bg-yellow-100 p-3 rounded-md h-min max-h-40 overflow-y-auto"></ul>
                <p id="cartTotal" class="mt-2 font-semibold">Toplam: ₺0</p>
                <a href="javascript:;" id="checkout" class="inline-block bg-blue-500 text-white font-medium py-2 px-4 rounded my-2">
                    Kasaya İlerle
                </a>
            </div>

            <div class="flex justify-between items-center space-x-2">
                <button id="addToCart" class="bg-green-500 text-white font-medium p-2 rounded w-1/2">Sepete Ekle</button>
                <button id="zappla" class="bg-yellow-500 text-dark font-semibold p-2 rounded w-1/2">Zappla!</button>
            </div>
        </div>
    </div>


    <script>
    // PHP'den gelen ürün verileri
    const products = <?php echo json_encode(array_values($products)); ?>;
    let productIndices = Array.from(products.keys());
    let currentProductIndex = 0;

    // DOM öğeleri
    const productName = document.getElementById('productName');
    const category = document.getElementById('category');
    const price = document.getElementById('price');
    const features = document.getElementById('features');
    const swiperContainer = document.getElementById('swiper-container');
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const cart = document.getElementById('cart');

    // Cookie'den sepeti yükle
    function loadCart() {
        const cartData = getCookie('cart');
        let totalPrice = 0;

        if (cartData) {
            const cartArray = JSON.parse(cartData);
            const productCount = {};

            cartArray.forEach(item => {
                totalPrice += parseFloat(item.price);
                productCount[item.name] = (productCount[item.name] || 0) + 1;
            });

            cartItems.innerHTML = Object.keys(productCount).map(name => {
                const count = productCount[name];
                const price = cartArray.find(item => item.name === name).price;
                return `
                    <li>
                        ${name} ${count > 1 ? `x${count}` : ''} - ₺${parseFloat(price).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} 
                        <button class="block remove-item bg-yellow-300 rounded-md px-3 py-1 font-medium" data-name="${name}">Sil</button>
                    </li>`;
            }).join('');
            cart.style.display = 'block';
        } else {
            cartItems.innerHTML = '<li>Sepet boş.</li>';
            cart.style.display = 'none';
        }

        cartTotal.textContent = `Toplam: ₺${totalPrice.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TL`;
        addRemoveListeners();
    }

    function saveCart(cart) {
        document.cookie = `cart=${encodeURIComponent(JSON.stringify(cart))}; path=/`;
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    }

    function loadProduct() {
        fetch('get_product.php')
            .then(response => response.json())
            .then(product => {
                if (product) {
                    productName.textContent = product.name;
                    category.textContent = `Kategori: ${product.category}`;
                    price.textContent = `Fiyat: ₺${product.price}`;
                    features.innerHTML = product.features.map(feature => `<li>${feature}</li>`).join('');

                    // Swiper için resimleri yükle
                    const swiperWrapper = document.querySelector('.swiper-wrapper');
                    swiperWrapper.innerHTML = ''; // Önceki resimleri temizle

                    product.images.forEach(image => {
                        const slide = document.createElement('div');
                        slide.classList.add('swiper-slide');
                        slide.innerHTML = `<img src="${image}" alt="${product.name}" class="w-full h-full object-cover">`;
                        swiperWrapper.appendChild(slide);
                    });

                    swiper.update(); // Swiper'ı güncelle

                    // URL'yi güncelle
                    const newUrl = `${window.location.origin}/${product.slug}`;
                    history.pushState({ productIndex: product.id }, '', newUrl);
                }
            });
    }

    window.addEventListener('popstate', (event) => {
        if (event.state && event.state.productIndex !== undefined) {
            currentProductIndex = event.state.productIndex;
            loadProduct(currentProductIndex);
        }
    });


    // Ürünleri karıştır
    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    // Karıştırılmış ürünleri yükle
    productIndices = shuffleArray(productIndices);

    productIndices = shuffleArray(productIndices);

    document.getElementById('zappla').addEventListener('click', () => {
        if (productIndices.length === 0) {
            alert('Tüm ürünler gösterildi!');
            return;
        }
        currentProductIndex = productIndices.shift();
        loadProduct(currentProductIndex);
    });

    // Swiper'ı başlat
    const swiper = new Swiper('.swiper-container', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Sepete Ekle butonu
    document.getElementById('addToCart').addEventListener('click', () => {
        const product = products[currentProductIndex];
        const cart = getCookie('cart') ? JSON.parse(getCookie('cart')) : [];
        cart.push({ id: product.id, name: product.name, price: product.price });
        saveCart(cart);
        loadCart();

        alert(`${product.name} sepete eklendi!`);
    });

    function removeFromCart(name) {
        const cart = getCookie('cart') ? JSON.parse(getCookie('cart')) : [];
        const itemIndex = cart.findIndex(item => item.name === name);
        
        if (itemIndex > -1) {
            cart[itemIndex].count--;
            if (cart[itemIndex].count <= 0) {
                cart.splice(itemIndex, 1);
            }
        }
        
        saveCart(cart);
        loadCart();
        alert('Ürün sepetten kaldırıldı.');
    }

    function addRemoveListeners() {
        const removeButtons = document.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const productName = e.target.getAttribute('data-name');
                let cart = getCookie('cart') ? JSON.parse(getCookie('cart')) : [];
                
                cart = cart.filter(item => item.name !== productName);
                saveCart(cart);
                loadCart();
                
                alert(`Ürün silindi: ${productName}`);
            });
        });
    }

    document.getElementById('toggleCart').addEventListener('click', () => {
        const cart = document.getElementById('cartItems');
        const cartChevron = document.getElementById('cartChevron');

        if (cart.style.display === 'block') {
            cart.style.display = 'none';
            cartChevron.setAttribute('transform', 'rotate(0 12 12)');
        } else {
            cart.style.display = 'block';
            cartChevron.setAttribute('transform', 'rotate(0 12 12)');
        }
    });

    document.getElementById('cartItems').style.display = 'none';
    loadCart();
    loadProduct(productIndices.shift());

    document.getElementById('checkout').addEventListener('click', () => {
        window.location.href = '/checkout';
    });

</script>


</body>
</html>
