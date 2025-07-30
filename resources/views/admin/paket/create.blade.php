<x-layoute-admin>
    <x-navbar-admin /><br>

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Tambah Paket Baru</h2>

        {{-- Error khusus gambar --}}
        @error('image_paket')
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                <strong>Error Gambar:</strong> {{ $message }}
            </div>
        @enderror

        <form action="{{ route('admin.paket.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700">Kode Paket</label>
                    <input type="text" name="kode_paket" value="{{ old('kode_paket') }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Nama Paket</label>
                    <input type="text" name="nama_paket" value="{{ old('nama_paket') }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Detail Paket</label>
                    <textarea name="detail_paket" class="w-full border border-gray-300 p-2 rounded" rows="4" required>{{ old('detail_paket') }}</textarea>
                </div>

                <div>
                    <label class="block text-gray-700">Kategori</label>
                    <select name="kategori_paket" class="w-full border border-gray-300 p-2 rounded" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="basic" {{ old('kategori_paket') == 'basic' ? 'selected' : '' }}>Paket Basic
                        </option>
                        <option value="special" {{ old('kategori_paket') == 'special' ? 'selected' : '' }}>Paket Special
                        </option>
                        <option value="family" {{ old('kategori_paket') == 'family' ? 'selected' : '' }}>Paket Family
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700">Harga</label>
                    <input type="number" name="harga_paket" value="{{ old('harga_paket') }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Stok</label>
                    <input type="number" name="stock_paket" value="{{ old('stock_paket') }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Upload Gambar</label>
                    <p class="text-sm text-gray-500">*Maksimal file 5MB</p>
                    <input type="file" name="image_paket"
                        class="w-full border p-2 rounded @error('image_paket') border-red-500 @enderror">
                </div>
            </div>

            <div class="mt-6 text-right">
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Simpan</button>
            </div>
        </form>
    </div>
</x-layoute-admin>
