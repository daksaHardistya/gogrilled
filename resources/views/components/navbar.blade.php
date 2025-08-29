<!-- Navbar Bootstrap yang responsif dan terstruktur rapi -->
<nav class="navbar navbar-expand-lg bg-black text-white shadow-lg fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="/">
            <img src="../img/logos/logo-go.grill.png" alt="Go Grill Logo" style="height: 40px;" />
        </a>

        <!-- Tombol menu hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <!-- Menu Navigasi -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link text-white fw-bold" href="/">HOME</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white fw-bold" href="/paket">PAKET</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white fw-bold" href="/produk">PRODUK</a>
                </li>
                {{-- <li class="nav-item position-relative">
                    <a class="nav-link" href="/cart" aria-label="Cart">
                        <img src="img/cart-icon.png" alt="Cart Icon" style="height: 24px;" />
                        <i class="fas fa-shopping-cart"></i>

                        <span id="cart-count"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size: 0.7rem; transform: translate(-50%, -30%);">
                            0
                        </span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</nav>


