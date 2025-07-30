<x-layoute-admin>
    <x-navbar-admin></x-navbar-admin><br>
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Edit Paket</h2>
        {{-- Error khusus gambar --}}
        @error('image_paket')
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                <strong>Error Gambar:</strong> {{ $message }}
            </div>
        @enderror
        <form action="{{ route('admin.paket.update', $paket->id_paket) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700">Nama Paket</label>
                    <input type="text" name="nama_paket" value="{{ $paket->nama_paket }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Detail Paket</label>
                    <textarea name="detail_paket" class="w-full border border-gray-300 p-2 rounded" rows="4" required>{{ $paket->detail_paket }}</textarea>
                </div>

                <div>
                    <label class="block text-gray-700">Kategori</label>
                    <select name="kategori_paket" class="w-full border border-gray-300 p-2 rounded" required>
                        <option value="{{ $paket->kategori_paket }}">-- Pilih Kategori --</option>
                        <option value="basic">Paket Basic</option>
                        <option value="special">Paket Special</option>
                        <option value="family">Paket Family</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700">Harga</label>
                    <input type="number" name="harga_paket" value="{{ $paket->harga_paket }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Stok</label>
                    <input type="number" name="stock_paket" value="{{ $paket->stock_paket }}"
                        class="w-full border border-gray-300 p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-gray-700">Gambar (optional)</label>
                    <p>*Maksimal file 5MB</p>
                    <input type="file" name="image_paket" class="w-full border border-gray-300 p-2 rounded">
                    @if ($paket->image_paket)
                        <img src="{{ asset('storage/' . $paket->image_paket) }}"
                            class="w-20 h-20 rounded object-cover mt-2">
                    @endif
                </div>
            </div>
            <div class="mt-6 text-right">
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Perbarui</button>
            </div>
        </form>
    </div>
</x-layoute-admin>
