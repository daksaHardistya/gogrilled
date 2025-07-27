<x-layoute-admin>
    <x-navbar-admin></x-navbar-admin><br>

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Daftar Produk</h1>
            <a href="{{ route('admin.produk.create') }}"
                class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Produk
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Kode Produk</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Harga</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Stok</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Gambar</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm">
                    @forelse ($produkList as $produk)
                        <tr>
                            <td class="px-6 py-4 text-gray-600">{{ $produk->kode_produk }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $produk->nama_produk }}</td>
                            <td class="px-6 py-4 text-gray-800">
                                Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.produk.stock.update', $produk->id_produk) }}"
                                    method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="stock_produk" value="{{ $produk->stock_produk }}"
                                        class="w-20 border border-gray-300 p-1 rounded text-center">
                                    <button type="submit"
                                        class="text-sm text-white bg-green-600 px-2 py-1 rounded hover:bg-green-700">Update</button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                @if ($produk->image_produk)
                                    <img src="{{ asset('storage/' . $produk->image_produk) }}" alt="Gambar Produk"
                                        class="w-16 h-16 object-cover rounded cursor-pointer"
                                        onclick="openDetail('{{ $produk->nama_produk }}', '{{ asset('storage/' . $produk->image_produk) }}')">
                                @else
                                    <span class="text-gray-400 italic">Tidak ada gambar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('admin.produk.edit', $produk->id_produk) }}"
                                    class="inline-flex items-center justify-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7.5 21H3v-4.5L16.732 3.732z" />
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.produk.delete', $produk->id_produk) }}" method="GET"
                                    onsubmit="return confirm('Yakin hapus produk?')" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-6 0a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1a1 1 0 01-1 1" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada produk tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalDetail" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
            <button onclick="closeDetail()"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600">&times;</button>
            <h2 id="detailNama" class="text-xl font-bold text-gray-800 mb-2"></h2>
            <img id="detailImage" src="" class="w-full h-48 object-cover rounded mb-4">
        </div>
    </div>

    <script>
        function openDetail(nama, image) {
            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailImage').src = image;
            document.getElementById('modalDetail').classList.remove('hidden');
            document.getElementById('modalDetail').classList.add('flex');
        }

        function closeDetail() {
            document.getElementById('modalDetail').classList.add('hidden');
            document.getElementById('modalDetail').classList.remove('flex');
        }
    </script>
</x-layoute-admin>
