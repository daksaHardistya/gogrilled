<x-layoute>
    <x-navbar></x-navbar>
    <div class="produkSatuan">
        <h1 class="heading-produk">PRODUK</h1>
        <form id="produkForm">
            <div class="menu-items">
                @foreach ($produkSatuan as $row)
                    <div class="produkSatuan-item" data-id="{{ $row->id_produk }}">
                        <img class="img-produk" src="{{ asset('storage/' . $row->image_produk) }}"
                            alt="{{ $row->nama_produk }}">
                        <h5 class="card-title nama_produk">{{ $row->nama_produk }}</h5>
                        <h6>Rp. {{ number_format($row->harga_produk, 0, ',', '.') }}</h6>

                        @if ($row->stock_produk > 0)
                            <div class="mt-2">
                                <button type="button"
                                    class="paket-btn bg-green-600 text-white px-3 py-1 rounded-md text-sm"
                                    data-id="{{ $row->id_produk }}"
                                    data-name="{{ $row->nama_produk }}"
                                    data-price="{{ $row->harga_produk }}"
                                    data-stok="{{ $row->stock_produk }}"
                                    data-selected="false">
                                    ✔ Pilih
                                </button>
                            </div>

                            <div class="mt-2" style="display: none;" id="jumlah-wrapper-{{ $row->id_produk }}">
                                <label for="jumlah{{ $row->id_produk }}" class="label-jumlah text-sm">Jumlah:</label>
                                <input id="jumlah{{ $row->id_produk }}" type="number" class="jumlah-pesanan"
                                    data-id="{{ $row->id_produk }}" min="1" value="1"
                                    max="{{ $row->stock_produk }}" style="width: 60px;">
                            </div>
                        @else
                            <h3 style="color: red;">{{ $row->nama_produk }} -
                                <span style="font-weight: bold;">Stok Habis</span>
                            </h3>
                        @endif
                    </div>
                @endforeach
            </div><br>

            @csrf
            <div class="fixed-btn justify-between mt-4">
                <x-backbutton />
                <button type="button" class="btn-next tombolnext" id="buttonnext">
                    Skip <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
            
        </form>
    </div>
</x-layoute>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".paket-btn");
        const buttonnext = document.getElementById("buttonnext");

        // default Skip
        buttonnext.innerHTML = 'Skip <i class="fas fa-angle-double-right"></i>';

        buttons.forEach(button => {
            button.addEventListener("click", function() {
                const id_produk = this.dataset.id;
                const jumlahWrapper = document.getElementById(`jumlah-wrapper-${id_produk}`);
                const selected = this.dataset.selected === "true";

                if (selected) {
                    this.dataset.selected = "false";
                    this.innerHTML = "✔ Pilih";
                    this.classList.remove("bg-red-600");
                    this.classList.add("bg-green-600");
                    jumlahWrapper.style.display = "none";
                } else {
                    this.dataset.selected = "true";
                    this.innerHTML = "❌ Batal";
                    this.classList.remove("bg-green-600");
                    this.classList.add("bg-red-600");
                    jumlahWrapper.style.display = "block";
                }

                const adaYangDipilih = Array.from(buttons).some(btn => btn.dataset.selected === "true");
                buttonnext.innerHTML = adaYangDipilih ?
                    'Next <i class="fas fa-arrow-right"></i>' :
                    'Skip <i class="fas fa-angle-double-right"></i>';
            });
        });

        buttonnext.addEventListener("click", () => {
            const produkDipilih = [];
            buttons.forEach(button => {
                if (button.dataset.selected === "true") {
                    const id = button.dataset.id;
                    const nama = button.dataset.name;
                    const harga = parseInt(button.dataset.price);
                    const jumlahInput = document.getElementById(`jumlah${id}`);
                    const jumlah = parseInt(jumlahInput.value);
                    const maxStokProduk = parseInt(jumlahInput.getAttribute("max"));
                    const card = document.querySelector(`.produkSatuan-item[data-id="${id}"]`);
                    const img = card.querySelector(".img-produk")?.src || '';

                    if (jumlah > maxStokProduk) {
                        alert(`Jumlah produk "${nama}" melebihi stok tersedia (${maxStokProduk}).`);
                        return;
                    }

                    produkDipilih.push({
                        id_produk: id,
                        nama_produk: nama,
                        harga_produk: harga,
                        jumlah_produk: jumlah,
                        stok_produk: maxStokProduk,
                        image_produk: img
                    });
                }
            });

            const existingData = JSON.parse(localStorage.getItem("produk_dipilih") || "[]");

            produkDipilih.forEach(produk => {
                const existing = existingData.find(p => p.id_produk === produk.id_produk);
                if (existing) {
                    existing.jumlah_produk += produk.jumlah_produk;
                } else {
                    existingData.push(produk);
                }
            });

            localStorage.setItem("produk_dipilih", JSON.stringify(existingData));

            if (produkDipilih.length > 0) {
                alert("Produk berhasil ditambahkan ke keranjang.");
                window.scrollTo({ top: 0, behavior: 'smooth' });
                window.location.href = "/cart";
            } else {
                window.location.href = "/cart";
            }
        });
    });
</script>
