<x-layoute>
    <x-navbar></x-navbar>
    <div class="produkSatuan">
        <h1 class="heading-produk">PRODUK SATUAN</h1>
        <form id="produkForm" action="cart" method="GET">
            <div class="menu-items">
                @foreach ($produkSatuan as $row)
                    <div class="produkSatuan-item" data-id="{{ $row->id_produk }}">
                        <img class="img-produk" src="{{ asset('../storage/' . $row->image_produk) }}"
                            alt="{{ $row->nama_produk }}">
                        <h5 class="card-title nama_produk">{{ $row->nama_produk }}</h5>
                        <h6>Rp. {{ number_format($row->harga_produk, 0, ',', '.') }}</h6>

                        @if ($row->stock_produk > 0)
                            <div class="flex items-center gap-2 mt-2">
                                <input class="checklis w-5 h-5" id="checkbox{{ $row->id_produk }}" type="checkbox"
                                    data-id="{{ $row->id_produk }}" data-name="{{ $row->nama_produk }}"
                                    data-price="{{ $row->harga_produk }}">
                                <label for="checkbox{{ $row->id_produk }}" class="text-sm">Pilih</label>
                            </div>

                            <div class="mt-2" style="display: none;" id="jumlah-wrapper-{{ $row->id_produk }}">
                                <label for="jumlah{{ $row->id_produk }}" class="label-jumlah text-sm">Jumlah:</label>
                                <input id="jumlah{{ $row->id_produk }}" type="number" class="jumlah-pesanan"
                                    data-id="{{ $row->id_produk }}" min="1" value="1"
                                    max="{{ $row->stock_produk }}" data-stok="{{ $row->stock_produk }}"
                                    style="width: 60px;">
                            </div>
                        @else
                            <h3 style="color: red;">{{ $row->nama_produk }} - <span style="font-weight: bold;">Stok
                                    Habis</span></h3>
                        @endif
                    </div>
                @endforeach
            </div><br>

            @csrf
            <div class="fixed-btn justify-content-between mt-4">
                <x-backbutton />
                <input type="hidden" name="produk_terpilih" id="produkTerpilih" value="">
                <button type="submit" class="btn-next tombolnext" id="buttonnext">Skip <i
                        class="fas fa-angle-double-right"></i></button>
            </div>
            <x-contact></x-contact>
        </form>
    </div>
</x-layoute>

<!-- === SCRIPT === -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll(".checklis");
        const buttonnext = document.getElementById("buttonnext");

        // Set default tombol
        buttonnext.innerHTML = 'Skip <i class="fas fa-angle-double-right"></i>';

        // Handle perubahan checkbox
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                const id_produk = this.dataset.id;
                const jumlahWrapper = document.getElementById(`jumlah-wrapper-${id_produk}`);

                if (this.checked) {
                    jumlahWrapper.style.display = "block";
                } else {
                    jumlahWrapper.style.display = "none";
                }

                const adaYangDipilih = Array.from(checkboxes).some(cb => cb.checked);
                buttonnext.innerHTML = adaYangDipilih ?
                    'Next <i class="fas fa-arrow-right"></i>' :
                    'Skip <i class="fas fa-angle-double-right"></i>';
            });
        });

        // Handle submit form
        document.getElementById("produkForm").addEventListener("submit", function(e) {
            const produkDipilih = [];

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const id = checkbox.dataset.id;
                    const nama = checkbox.dataset.name;
                    const harga = checkbox.dataset.price;
                    const jumlahInput = document.getElementById(`jumlah${id}`);
                    const jumlah = parseInt(jumlahInput.value);
                    const stok = parseInt(jumlahInput.getAttribute("max"));
                    const card = document.querySelector(`.produkSatuan-item[data-id="${id}"]`);
                    const img = card.querySelector(".img-produk")?.src || '';

                    // Validasi stok
                    if (jumlah > stok) {
                        alert(`Jumlah produk "${nama}" melebihi stok tersedia (${stok}).`);
                        e.preventDefault();
                        return;
                    }

                    // Simpan semua data termasuk stok
                    produkDipilih.push({
                        id_produk: id,
                        nama_produk: nama,
                        harga_produk: parseInt(harga),
                        jumlah_produk: jumlah,
                        stok_produk: stok, // stok ikut disimpan
                        image_produk: img
                    });
                }
            });

            // Ambil data lama di localStorage
            let existingData = JSON.parse(localStorage.getItem("produk_dipilih")) || [];

            // Gabungkan data lama dengan baru
            for (let baru of produkDipilih) {
                let index = existingData.findIndex(p => p.id_produk === baru.id_produk);
                if (index !== -1) {
                    existingData[index].jumlah_produk += baru.jumlah_produk;
                    // Update stok terbaru juga
                    existingData[index].stok_produk = baru.stok_produk;
                } else {
                    existingData.push(baru);
                }
            }

            if (produkDipilih.length > 0) {
                localStorage.setItem("produk_dipilih", JSON.stringify(existingData));
                alert("Produk berhasil ditambahkan ke keranjang.");
            }
        });
    });
</script>
