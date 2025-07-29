<x-layoute>
    <x-navbar></x-navbar><br>
    <div class="body-cart">
        <div class="cart-container">
            <h2 class="cart-title">KERANJANG BELANJA</h2>
            <!-- Paket -->
            <section class="cart-section">
                <h3 class="cart-subtitle">Paket yang dipesan:</h3>
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Paket</th>
                                <th>Harga Satuan</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-paket"></tbody>
                    </table>
                </div>
            </section>

            <!-- Produk -->
            <section class="cart-section">
                <h3 class="cart-subtitle">Produk yang dipesan:</h3>
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga Satuan</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tabel-produk"></tbody>
                    </table>
                </div>
            </section>

            <!-- Total dan Tombol -->
            <div class="cart-footer">
                <div id="total-belanja" class="cart-total">Total: <strong>Rp. 0</strong></div>
                <div class="cart-actions">
                    <button onclick="history.back()" class="btn-secondary"><i class="fas fa-arrow-left"></i>
                        Back</button>
                    <button id="btn-checkout" onclick="checkout()" class="btn-checkout">Checkout <i
                            class="fas fa-arrow-right-to-bracket"></i></button>
                </div>
            </div>
        </div>
        <x-contact></x-contact>
    </div>
</x-layoute>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let paketDipilih = JSON.parse(localStorage.getItem("paket_dipilih")) || [];
        let produkDipilih = JSON.parse(localStorage.getItem("produk_dipilih")) || [];

        const tabelPaket = document.getElementById("tabel-paket");
        const tabelProduk = document.getElementById("tabel-produk");
        const totalBelanja = document.getElementById("total-belanja");
        const tombolCheckout = document.getElementById("btn-checkout");

        function simpanKeLocalStorage() {
            localStorage.setItem("paket_dipilih", JSON.stringify(paketDipilih));
            localStorage.setItem("produk_dipilih", JSON.stringify(produkDipilih));
        }

        function hitungSubtotal(harga, jumlah) {
            return harga * jumlah;
        }

        function renderTabel() {
            tabelPaket.innerHTML = "";
            tabelProduk.innerHTML = "";
            let grandTotal = 0;

            // Paket
            paketDipilih.forEach((paket) => {
                const subtotal = hitungSubtotal(paket.harga_paket, paket.jumlah_paket);
                grandTotal += subtotal;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><img src="${paket.image_paket}" width="70"></td>
                    <td>${paket.nama_paket}</td>
                    <td>Rp. ${paket.harga_paket.toLocaleString("id-ID")}</td>
                    <td>
                        <button class="btn-action" onclick="kurangPaket(${paket.id_paket})">−</button>
                        <span class="qty-text">${paket.jumlah_paket}</span>
                        <button class="btn-action" onclick="tambahPaket(${paket.id_paket})">+</button>
                    </td>
                    <td>Rp. ${subtotal.toLocaleString("id-ID")}</td>
                    <td>
                        <button class="btn-delete" onclick="hapusPaket(${paket.id_paket})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tabelPaket.appendChild(row);
            });

            // Produk
            produkDipilih.forEach((produk) => {
                const subtotal = hitungSubtotal(produk.harga_produk, produk.jumlah_produk);
                grandTotal += subtotal;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><img src="${produk.image_produk}" width="70"></td>
                    <td>${produk.nama_produk}</td>
                    <td>Rp. ${produk.harga_produk.toLocaleString("id-ID")}</td>
                    <td>
                        <button class="btn-action" onclick="kurangProduk(${produk.id_produk})">−</button>
                        <span class="qty-text">${produk.jumlah_produk}</span>
                        <button class="btn-action" onclick="tambahProduk(${produk.id_produk})">+</button>
                    </td>
                    <td>Rp. ${subtotal.toLocaleString("id-ID")}</td>
                    <td>
                        <button class="btn-delete" onclick="hapusProduk(${produk.id_produk})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tabelProduk.appendChild(row);
            });

            totalBelanja.innerHTML = `
                <div class="bg-yellow-100 text-red-600 text-base font-semibold px-3 py-2 rounded w-fit text-end">
                    Total: Rp. ${grandTotal.toLocaleString("id-ID")}
                </div>
            `;
            tombolCheckout.disabled = (paketDipilih.length === 0 && produkDipilih.length === 0);
        }

        // Event Paket
        window.tambahPaket = function(id) {
            const item = paketDipilih.find(p => parseInt(p.id_paket) === parseInt(id));
            if (item) {
                if (item.jumlah_paket < item.stok_paket) { // pengecekan stok paket
                    item.jumlah_paket++;
                    simpanKeLocalStorage();
                    renderTabel();
                } else {
                    alert("Stok paket tidak mencukupi.");
                }
            }
        };

        window.kurangPaket = function(id) {
            const item = paketDipilih.find(p => parseInt(p.id_paket) === parseInt(id));
            if (item) {
                item.jumlah_paket--;
                if (item.jumlah_paket <= 0) {
                    paketDipilih = paketDipilih.filter(p => parseInt(p.id_paket) !== parseInt(id));
                }
                simpanKeLocalStorage();
                renderTabel();
            }
        };

        window.hapusPaket = function(id) {
            paketDipilih = paketDipilih.filter(p => parseInt(p.id_paket) !== parseInt(id));
            simpanKeLocalStorage();
            renderTabel();
        };

        // Event Produk
        window.tambahProduk = function(id) {
            const item = produkDipilih.find(p => parseInt(p.id_produk) === parseInt(id));
            if (item) {
                if (item.jumlah_produk < item.stok_produk) { // pengecekan stok produk
                    item.jumlah_produk++;
                    simpanKeLocalStorage();
                    renderTabel();
                } else {
                    alert("Stok produk tidak mencukupi.");
                }
            }
        };

        window.kurangProduk = function(id) {
            const item = produkDipilih.find(p => parseInt(p.id_produk) === parseInt(id));
            if (item) {
                item.jumlah_produk--;
                if (item.jumlah_produk <= 0) {
                    produkDipilih = produkDipilih.filter(p => parseInt(p.id_produk) !== parseInt(id));
                }
                simpanKeLocalStorage();
                renderTabel();
            }
        };

        window.hapusProduk = function(id) {
            produkDipilih = produkDipilih.filter(p => parseInt(p.id_produk) !== parseInt(id));
            simpanKeLocalStorage();
            renderTabel();
        };

        window.checkout = function() {
            if (paketDipilih.length === 0 && produkDipilih.length === 0) {
                alert("Silakan tambahkan produk atau paket terlebih dahulu sebelum checkout.");
                return;
            }
            window.location.href = "/form";
        };

        // Render awal
        renderTabel();
    });
</script>
