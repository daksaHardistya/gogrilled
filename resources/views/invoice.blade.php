<x-layoute>
    <div class="container my-5">
        <div class="card-invoice shadow-lg rounded-lg">
            <div class="card-body">
                <div class="tittle-invoice text-center">
                    <h2 class="text-danger">INVOICE PEMESANAN</h1>
                        <h1>GO.GRILLED SINGARAJA</h1>
                        <p class="text-muted">Terima kasih atas pesanan Anda</p>
                </div>

                <div class="data-pelanggan mb-4">
                    <h2 class="h5 text-dark border-bottom pb-2 mb-4">Informasi Pelanggan</h2>
                    <div><strong>Nama:</strong> <span id="customer-name">-</span></div>
                    <div><strong>Alamat:</strong> <span id="customer-address">-</span></div>
                    <div><strong>No. Telepon:</strong> <span id="customer-phone">-</span></div>
                    <div><strong>Email:</strong> <span id="customer-email">-</span></div>
                </div>

                <div class="detail-transaksi mb-4">
                    <h2 class="h5 text-dark border-bottom pb-2 mb-4">Detail Pembayaran</h2>
                    <div><strong>Nomor Transaksi:</strong> <span id="nomor-pembayaran">-</span></div>
                    <div><strong>Metode Pembayaran:</strong> <span id="tipe-pembayaran">-</span></div>
                </div>

                <div class="daftar-pesanan mb-4">
                    <h2 class="h5 text-dark border-bottom pb-2 mb-4">Daftar Pesanan</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table-light">
                                <th class="text-left">Nama Item</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="order-details"></tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td class="text-right">Total:</td>
                                <td class="text-right">Rp. <span style="font-size: medium" id="total-amount">0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <p>*SILAHKAN PILIH SELESAI SEBELUM MENUTUP APLIKASI, TERIMAKSIH</p>
                </div>

                <div class="container-btn-invoice flex items-center mt-4 space-x-4 ">
                    <button id="download-pdf" class="btn btn-danger bold"><i class="fas fa-file-pdf"></i>
                        Download</button>
                    <button id="finish-button" class="btn-sukses text-white rounded transition duration-200">Selesai <i
                            class="fas fa-check-circle"></i></button>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const customerData = JSON.parse(localStorage.getItem('customerData')) || {};
            const orderPaket = JSON.parse(localStorage.getItem('paket_dipilih')) || [];
            const orderProduk = JSON.parse(localStorage.getItem('produk_dipilih')) || [];
            const tipePembayaran = localStorage.getItem('tipe_pembayaran') || '-';
            const nomorPembayaran = localStorage.getItem('paymentNumber') || '-';

            document.getElementById('customer-name').textContent = customerData.nama || '-';
            document.getElementById('customer-address').textContent = customerData.alamat || '-';
            document.getElementById('customer-phone').textContent = customerData.nomor_tlp || '-';
            document.getElementById('customer-email').textContent = customerData.email || '-';
            document.getElementById('nomor-pembayaran').textContent = nomorPembayaran;
            document.getElementById('tipe-pembayaran').textContent = tipePembayaran;

            const orderTable = document.getElementById('order-details');
            orderTable.innerHTML = ''; // Bersihkan dulu isinya

            let totalHarga = 0;

            // Fungsi buat row agar lebih rapi
            function buatRow(namaItem, subtotal) {
                const tr = document.createElement('tr');
                const tdNama = document.createElement('td');
                tdNama.textContent = namaItem;
                const tdSubtotal = document.createElement('td');
                tdSubtotal.classList.add('text-right');
                tdSubtotal.textContent = `Rp. ${subtotal.toLocaleString('id-ID')}`;
                tr.appendChild(tdNama);
                tr.appendChild(tdSubtotal);
                return tr;
            }

            orderPaket.forEach(item => {
                const subtotal = item.jumlah_paket * item.harga_paket;
                totalHarga += subtotal;
                orderTable.appendChild(buatRow(`${item.nama_paket} x${item.jumlah_paket}`, subtotal));
            });

            orderProduk.forEach(item => {
                const subtotal = item.jumlah_produk * item.harga_produk;
                totalHarga += subtotal;
                orderTable.appendChild(buatRow(`${item.nama_produk} x${item.jumlah_produk}`, subtotal));
            });

            document.getElementById('total-amount').textContent = totalHarga.toLocaleString('id-ID');

            // Download PDF
            document.getElementById("download-pdf").addEventListener("click", function() {
                const combinedElement = document.createElement("div");
                combinedElement.appendChild(document.querySelector(".tittle-invoice").cloneNode(true));
                combinedElement.appendChild(document.querySelector(".data-pelanggan").cloneNode(true));
                combinedElement.appendChild(document.querySelector(".detail-transaksi").cloneNode(true));
                combinedElement.appendChild(document.querySelector(".daftar-pesanan").cloneNode(true));
                combinedElement.appendChild(document.querySelector(".table").cloneNode(true));

                const element = combinedElement;
                const opt = {
                    margin: [-1, 0.5, -1, 0.5],
                    filename: 'invoice_gogrilled.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                };

                html2pdf().set(opt).from(element).save();
            });

            // Tombol selesai
            document.getElementById("finish-button").addEventListener("click", function() {
                localStorage.removeItem('customerData');
                localStorage.removeItem('paket_dipilih');
                localStorage.removeItem('produk_dipilih');
                localStorage.removeItem('tipe_pembayaran');
                localStorage.removeItem('paymentNumber');
                window.location.href = '/';
            });
        });
    </script>
</x-layoute>
