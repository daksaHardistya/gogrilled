<x-layoute>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div
        class="card-status p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow-xl mx-auto max-w-sm sm:max-w-md md:max-w-xl lg:max-w-2xl mt-8 mb-8 border border-gray-200">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 mb-6 text-center tracking-tight">RINCIAN
            PEMBELANJAAN</h1>

        <div class="customer-details mb-6 p-4 sm:p-5 border border-blue-200 rounded-lg bg-blue-50">
            <h3 class="font-bold text-lg sm:text-xl text-blue-800 mb-3">Data Pelanggan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm sm:text-base text-gray-700">
                <div><strong>Nama:</strong> <span id="customer-name">-</span></div>
                <div><strong>Nomor WhatsApp:</strong> <span id="customer-phone">-</span></div>
                <div><strong>Alamat:</strong> <span id="customer-address">-</span></div>
                <div><strong>Email:</strong> <span id="customer-email">-</span></div>
                <div><strong>Nomor Transaksi:</strong> <span id="nomor-transaksi">-</span></div>
                <div><strong>Jenis Pembayaran:</strong> <span id="tipe-pembayaran">-</span></div>
            </div>
        </div>

        <div class="order-summary mb-6 p-4 sm:p-5 border border-green-200 rounded-lg bg-green-50">
            <h3 class="font-bold text-lg sm:text-xl text-green-800 mb-3">Rincian Pembelanjaan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border border-gray-300 rounded-lg overflow-hidden mb-4">
                    <thead class="bg-green-100">
                        <tr>
                            <th class="p-3 border border-gray-300 text-sm sm:text-base font-semibold text-gray-700">Nama
                                Item</th>
                            <th
                                class="p-3 border border-gray-300 text-right text-sm sm:text-base font-semibold text-gray-700">
                                Harga</th>
                        </tr>
                    </thead>
                    <tbody id="order-details" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
            <h3 class="text-right text-xl sm:text-2xl font-bold text-gray-800 mt-4">Total: Rp. <span
                    id="total-amount">0</span></h3>
        </div>

        <div class="payment-method mb-6 p-4 sm:p-5 border border-purple-200 rounded-lg bg-purple-50">
            <h3 class="font-semibold text-base sm:text-lg text-purple-800 mb-3">Pilih Metode Pembayaran:</h3>
            <div class="radio-group flex flex-col sm:flex-row sm:space-x-6">
                <label class="mb-2 sm:mb-0 flex items-center text-gray-700 text-base sm:text-lg">
                    <input id="cash" class="radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 mr-2"
                        type="radio" name="payment-method" value="Cash">
                    Cash <i class="fas fa-money-bill-wave ml-2 text-green-600"></i>
                </label>
                <label class="flex items-center text-gray-700 text-base sm:text-lg">
                    <input id="transfer" class="radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 mr-2"
                        type="radio" name="payment-method" value="Transfer Bank">
                    Transfer Bank <i class="fas fa-university ml-2 text-blue-600"></i>
                </label>
            </div>
        </div>

        <div id="upload-section" class="upload-section mb-6 p-4 sm:p-5 border border-yellow-200 rounded-lg bg-yellow-50"
            style="display: none;">
            <h5 class="font-semibold text-base sm:text-lg text-yellow-800 mb-2">Rekening Transfer:</h5>
            <h6 class="text-sm sm:text-base text-gray-800">a.n. KadeK Yuda Wiryanatha</h6>
            <h6 class="text-sm sm:text-base text-gray-800 mb-3">Bank BRI: 1122334455 a.n. Go Grilled</h6>
            <p class="text-red-600 text-xs sm:text-sm mb-4 italic">*Pastikan untuk menyimpan dan mengunggah bukti
                transfer Anda.</p>
            <h5 class="text-black font-bold mb-3 text-base sm:text-lg">Upload Bukti Transfer</h5>
            <p>*Maksimal file 2MB</p>
            <input type="file" id="proof-upload" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf"
                class="block w-full text-sm sm:text-base text-gray-700
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-full file:border-0
                       file:text-sm file:font-semibold
                       file:bg-blue-50 file:text-blue-700
                       hover:file:bg-blue-100"
                value="File belum diupload">
        </div>

        <div class="container-btn-metode">
            <x-backbutton />
            <button id="confirm-button" class="button-confirm text-base sm:text-lg w-full sm:w-auto"
                style="display: none;">
                Order <i class="fas fa-check-circle ml-2"></i>
            </button>

        </div>
        
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const customerData = JSON.parse(localStorage.getItem('customerData')) || {};
            const orderPaket = JSON.parse(localStorage.getItem('paket_dipilih')) || [];
            const orderProduk = JSON.parse(localStorage.getItem('produk_dipilih')) || [];
            let tipePembayaran = localStorage.getItem('tipe_pembayaran') || '';
            let buktiPembayaran = localStorage.getItem('bukti_pembayaran') || '';

            function generateNomorPembayaran(nomorTlp) {
                const now = new Date();
                const tanggal = now.toISOString().slice(0, 10).replace(/-/g, '');
                const angkaHP = (nomorTlp || '').replace(/\D/g, '');
                const akhirHP = angkaHP.slice(-4).padStart(4, '0');
                const randomNum = Math.floor(100 + Math.random() * 900);
                return `${tanggal}${akhirHP}${randomNum}`;
            }

            const nomorPembayaran = generateNomorPembayaran(customerData.nomor_tlp || '');
            localStorage.setItem('paymentNumber', nomorPembayaran);

            // Tampilkan data customer
            document.getElementById('customer-name').innerText = customerData.nama || '-';
            document.getElementById('customer-address').innerText = customerData.alamat || '-';
            document.getElementById('customer-phone').innerText = customerData.nomor_tlp || '-';
            document.getElementById('customer-email').innerText = customerData.email || '-';
            document.getElementById('nomor-transaksi').innerText = nomorPembayaran;
            document.getElementById('tipe-pembayaran').innerText = tipePembayaran || '-';

            const orderTable = document.getElementById('order-details');
            let totalHarga = 0;

            orderPaket.forEach(item => {
                const subtotal = item.jumlah_paket * item.harga_paket;
                orderTable.innerHTML += `
                    <tr>
                        <td class="p-3 border border-gray-200">${item.nama_paket} (x${item.jumlah_paket})</td>
                        <td class="p-3 border border-gray-200 text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                    </tr>`;
                totalHarga += subtotal;
            });

            orderProduk.forEach(item => {
                const subtotal = item.jumlah_produk * item.harga_produk;
                orderTable.innerHTML += `
                    <tr>
                        <td class="p-3 border border-gray-200">${item.nama_produk} (x${item.jumlah_produk})</td>
                        <td class="p-3 border border-gray-200 text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                    </tr>`;
                totalHarga += subtotal;
            });

            document.getElementById('total-amount').innerText = totalHarga.toLocaleString('id-ID');

            const radios = document.querySelectorAll('input[name="payment-method"]');
            const uploadSection = document.getElementById('upload-section');
            const tipePembayaranSpan = document.getElementById('tipe-pembayaran');
            const confirmButton = document.getElementById('confirm-button');
            const proofInput = document.getElementById('proof-upload');


            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    tipePembayaran = this.value;
                    localStorage.setItem("tipe_pembayaran", tipePembayaran);
                    tipePembayaranSpan.innerText = tipePembayaran;

                    if (this.id === "transfer") {
                        uploadSection.style.display = "block";
                    }
                    if (this.id === "cash") {
                        uploadSection.style.display = "none";
                        localStorage.setItem("bukti_pembayaran", "Cash");
                        confirmButton.style.display = "inline-block";
                    } else {
                        confirmButton.style.display = "none";
                    }
                });
            });

            proofInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) uploadBuktiTf(file);
            });

            function uploadBuktiTf(file) {
                const formData = new FormData();
                formData.append('bukti_pembayaran', file);

                fetch('/upload-bukti', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem("bukti_pembayaran", data.fileName);
                            alert("Bukti transfer berhasil diupload.");
                            confirmButton.style.display = "inline-block";
                        } else {
                            alert("Gagal mengupload bukti transfer.");
                        }
                    })
                    .catch(error => {
                        console.error("Upload Error:", error);
                        alert("Terjadi kesalahan saat upload bukti.");
                    });
            }

            confirmButton.addEventListener('click', function() {
                buktiPembayaran = tipePembayaran === 'Transfer Bank' ?
                    (localStorage.getItem("bukti_pembayaran") || 'Belum diupload') :
                    'Cash';

                const payload = {
                    pelanggan: {
                        nomor_tlp: customerData.nomor_tlp,
                        nama_pel: customerData.nama,
                        alamat_pel: customerData.alamat,
                        email_pel: customerData.email
                    },
                    order: {
                        nomor_transaksi: nomorPembayaran,
                        tipe_pembayaran: tipePembayaran,
                        total_belanjaan: totalHarga,
                        bukti_pembayaran: buktiPembayaran
                    },
                    orderPaket: orderPaket.map(item => ({
                        id_paket: item.id_paket,
                        jumlah_orderPaket: item.jumlah_paket
                    })),
                    orderProduk: orderProduk.map(item => ({
                        id_produk: item.id_produk,
                        jumlah_orderProduk: item.jumlah_produk
                    }))
                };

                fetch('/simpanTransaksi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Notifikasi WhatsApp
                        const adminPhone = '081938103934';
                        const fonnteToken = 'cFh96YKghJi8GQkN3LFN';
                        const pesanAdmin = `🛒 *Order Baru Masuk!*
*Data Pelanggan:*
• Nama    : ${customerData.nama}
• Telepon : ${customerData.nomor_tlp}
• Alamat  : ${customerData.alamat}
*Rincian Belanjaan:*\n
*Produk:*${orderProduk.map((item, i) => `${i + 1}. ${item.nama_produk} x${item.jumlah_produk} - Rp ${item.harga_produk.toLocaleString('id-ID')}`).join('\n')}${orderPaket.length > 0 ? `\n*Paket:*${orderPaket.map((item, i) => `${i + 1}. ${item.nama_paket} x${item.jumlah_paket} - Rp ${item.harga_paket.toLocaleString('id-ID')}`).join('\n')}` : ''}
*Pembayaran:*${tipePembayaran}
*Nomor Transaksi:*${nomorPembayaran}
*Total:*Rp ${totalHarga.toLocaleString('id-ID')}`;

                        fetch("https://api.fonnte.com/send", {
                            method: "POST",
                            headers: {
                                "Authorization": fonnteToken
                            },
                            body: new URLSearchParams({
                                target: adminPhone,
                                message: pesanAdmin
                            })
                        });

                        alert("Pesanan berhasil dilakukan, tunggu konfirmasi dari admin kami.");
                        window.location.href = "/invoice"; // Redirect ke invoice / success page
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Gagal menyimpan transaksi.");
                    });
            });
        });
    </script>
</x-layoute>
