<x-layoute>
    <x-navbar></x-navbar>
    <div class="container-form">
        <h1>Form Pemesanan</h1>
        <form id="orderForm">
            @csrf

            <label class="label-form" for="nama">Nama:</label>
            <p>*Masukkan nama yang sesuai</p>
            <input class="input-place" id="nama_pel" type="text" name="nama" placeholder="Tulis nama lengkap kamu.."
                required><br><br>

            <label class="label-form" for="no-telp">WhatsApp:</label>
            <p>*Masukkan nomor WhatsApp yang benar</p>
            <input class="input-place" id="nomor_tlp" type="tel" name="no-telp" placeholder="Contoh: 081234567890"
                pattern="^08[0-10]{8,11}$" title="Nomor harus dimulai dengan 08 dan memiliki 11 hingga 13 digit angka"
                required><br><br>

            <label class="label-form" for="email">Email:</label>
            <p>*Masukkan email yang benar</p>
            <input class="input-place" id="email_pel" type="email" name="email" placeholder="Contoh: nama@email.com"
                required><br><br>

            <label class="label-form" for="alamat">Alamat:</label>
            <p>*Masukkan alamat lengkap untuk pengiriman</p>
            <input class="input-place" id="alamat_pel" type="text" name="alamat"
                placeholder="Tulis alamat lengkap.." required><br><br>

            <label class="label-form" for="pesanan">Pesanan:</label>
            <li id="checkbox-list"></li>
            <br>

            <h4>Total: Rp. <span id="total-amount">0</span></h4>
            <div class="flex justify-between mt-4">
                <x-backbutton />
                <button class="btn-next right-btn" type="button" id="submit-button">Next <i
                        class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
    
</x-layoute>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const savedData = JSON.parse(localStorage.getItem("customerData")) || {};
        document.getElementById("nama_pel").value = savedData.nama || "";
        document.getElementById("nomor_tlp").value = savedData.nomor_tlp || "";
        document.getElementById("email_pel").value = savedData.email || "";
        document.getElementById("alamat_pel").value = savedData.alamat || "";

        const produkDipilih = JSON.parse(localStorage.getItem("produk_dipilih")) || [];
        const paketDipilih = JSON.parse(localStorage.getItem("paket_dipilih")) || [];
        const listElement = document.getElementById("checkbox-list");
        const totalElement = document.getElementById("total-amount");
        let totalHarga = 0;

        function tambahkanItem(nama, jumlah, harga) {
            const li = document.createElement("li");
            li.textContent = `${nama} (x${jumlah}) - Rp. ${harga.toLocaleString()}`;
            listElement.appendChild(li);
            totalHarga += harga * jumlah;
        }

        paketDipilih.forEach(item => tambahkanItem(item.nama_paket, item.jumlah_paket, item.harga_paket));
        produkDipilih.forEach(item => tambahkanItem(item.nama_produk, item.jumlah_produk, item.harga_produk));

        if (paketDipilih.length === 0 && produkDipilih.length === 0) {
            listElement.innerHTML = "<li>Tidak ada pesanan dipilih.</li>";
        }

        totalElement.textContent = totalHarga.toLocaleString();
    });

    function simpanData() {
        const customerData = {
            nama: document.getElementById("nama_pel").value.trim(),
            nomor_tlp: document.getElementById("nomor_tlp").value.trim(),
            email: document.getElementById("email_pel").value.trim(),
            alamat: document.getElementById("alamat_pel").value.trim()
        };
        localStorage.setItem("customerData", JSON.stringify(customerData));
    }

    function isValidNomor(nomor) {
        const pattern = /^08[0-9]{8,11}$/;
        return pattern.test(nomor);
    }

    function submitOrder() {
        const nomor = document.getElementById("nomor_tlp").value.trim();
        if (!isValidNomor(nomor)) {
            alert("Nomor WhatsApp tidak valid!\nHarus dimulai dengan 08 dan terdiri dari 10 hingga 13 digit angka.");
            document.getElementById("nomor_tlp").focus();
            return;
        }
        const nama = document.getElementById("nama_pel").value.trim();
        const email = document.getElementById("email_pel").value.trim();
        const alamat = document.getElementById("alamat_pel").value.trim();
        if (!nomor) {
            alert("Nomor WhatsApp tidak boleh kosong!");
            document.getElementById("nomor_tlp").focus();
            return;
        }
        if (!nama) {
            alert("Nama tidak boleh kosong!");
            document.getElementById("nama_pel").focus();
            return;
        }
        if (!email) {
            alert("Email tidak boleh kosong!");
            document.getElementById("email_pel").focus();
            return;
        }
        if (!alamat) {
            alert("Alamat tidak boleh kosong!");
            document.getElementById("alamat_pel").focus();
            return;
        } else {
            if (!isValidNomor(nomor)) {
                alert(
                    "Nomor WhatsApp tidak valid!\nHarus dimulai dengan 08 dan terdiri dari 10 hingga 13 digit angka."
                );
                document.getElementById("nomor_tlp").focus();
                return;
            }
        }

        simpanData();
        alert("Pesanan sudah terkirim!");
        window.location.href = "/metodePembayaran";
    }

    document.getElementById("submit-button").addEventListener("click", submitOrder);
</script>
