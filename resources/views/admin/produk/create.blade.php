<x-layoute-admin>
    <x-navbar-admin></x-navbar-admin><br>
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Tambah produk Baru</h2>
        @error('image_produk')
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                <strong>Error Gambar:</strong> {{ $message }}
            </div>
        @enderror
        <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700">Kode produk</label>
                    <input type="text" name="kode_produk" class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Nama produk</label>
                    <input type="text" name="nama_produk" class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Harga</label>
                    <input type="number" name="harga_produk" class="w-full border border-gray-300 p-2 rounded"
                        required>
                </div>

                <div>
                    <label class="block text-gray-700">Stok</label>
                    <input type="number" name="stock_produk" class="w-full border border-gray-300 p-2 rounded"
                        required>
                </div>

                <div>
                    <label class="block text-gray-700">Gambar</label>
                    <p>*Maksimal file 5MB</p>
                    <input type="file" name="image_produk" class="w-full border border-gray-300 p-2 rounded">
                </div>
            </div>
            <div class="mt-6 text-right">
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Simpan</button>
            </div>
        </form>
    </div>
</x-layoute-admin>
