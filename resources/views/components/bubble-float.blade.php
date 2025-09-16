<!-- Bubble Icons (WhatsApp & Cart) -->
<!-- Bubble Icons (WhatsApp & Cart) -->
<div class="bubble-container">
    <!-- Cart -->
    <div class="bubble-icon bubble-cart">
        <a class="nav-link" href="/cart" aria-label="Cart">
            <i class="fa-solid fa-cart-shopping fa-lg"></i>
            <span id="cart-count">0</span>
        </a>
    </div>

    <!-- WhatsApp -->
    <div class="bubble-icon bubble-wa">
        <a href="https://wa.me/6281938103934" target="_blank" aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp fa-2x"></i>
        </a>
    </div>
</div>


<script>
    function updateCartCount() {
        const paket = JSON.parse(localStorage.getItem('paket_dipilih') || '[]');
        const produk = JSON.parse(localStorage.getItem('produk_dipilih') || '[]');

        let totalCount = 0;

        paket.forEach(item => totalCount += item.jumlah_paket || 1);
        produk.forEach(item => totalCount += item.jumlah_produk || 1);

        const countElement = document.getElementById('cart-count');
        if (countElement) {
            countElement.textContent = totalCount;
            countElement.style.display = totalCount > 0 ? 'flex' : 'none';
        }
    }

    // Jalankan saat halaman pertama kali load
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // Dengarkan perubahan localStorage dari tab lain (realtime antar tab/browser)
    window.addEventListener('storage', function(event) {
        if (event.key === 'paket_dipilih' || event.key === 'produk_dipilih') {
            updateCartCount();
        }
    });

    // Override localStorage.setItem supaya update realtime di tab yang sama
    const originalSetItem = localStorage.setItem;
    localStorage.setItem = function(key, value) {
        originalSetItem.apply(this, arguments);
        if (key === 'paket_dipilih' || key === 'produk_dipilih') {
            updateCartCount();
        }
    };

    // Kalau pakai localStorage.removeItem juga perlu
    const originalRemoveItem = localStorage.removeItem;
    localStorage.removeItem = function(key) {
        originalRemoveItem.apply(this, arguments);
        if (key === 'paket_dipilih' || key === 'produk_dipilih') {
            updateCartCount();
        }
    };
</script>
