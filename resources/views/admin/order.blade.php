<x-layoute-admin>
    <x-navbar-admin></x-navbar-admin><br>

    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Daftar Order</h1>
        {{-- Form Filter --}}
        <form method="GET" class="mb-6 flex flex-wrap items-center gap-4">
            <div>
                <label for="inaktif" class="text-sm font-medium text-gray-700">Pelanggan Order Terakhir:</label>
                <select name="inaktif" id="inaktif" class="border rounded px-3 py-2 shadow-sm">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" {{ request('inaktif') == $i ? 'selected' : '' }}>
                            {{ $i }} bulan lalu
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="cari" class="text-sm font-medium text-gray-700">Cari Pelanggan:</label>
                <input type="text" name="cari" id="cari" value="{{ request('cari') }}"
                    placeholder="Nama atau Nomor HP" class="border rounded px-3 py-2 shadow-sm">
            </div>

            <div>
                <label for="bulan" class="text-sm font-medium text-gray-700">Filter Bulan & Tahun:</label>
                <input type="month" name="bulan" id="bulan" value="{{ request('bulan') }}"
                    class="border rounded px-3 py-2 shadow-sm">
            </div>

            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Filter
            </button>
        </form>


        @php
            // Kumpulkan data nomor telepon yang duplikat beserta tanggal ordernya
            $duplicateData = $orders
                ->groupBy(fn($o) => $o->data_pelanggan->nomor_tlp ?? 'Tanpa Nomor')
                ->filter(fn($group) => $group->count() > 1)
                ->map(function ($group) {
                    return $group
                        ->pluck('created_at')
                        ->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M Y'))
                        ->unique()
                        ->values()
                        ->toArray();
                })
                ->toArray();
        @endphp

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Nomor Transaksi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Nama Pelanggan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Pesanan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Total</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Tanggal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm">
                    @forelse ($orders as $order)
                        @php
                            $nomor = $order->data_pelanggan->nomor_tlp ?? 'Tanpa Nomor';
                        @endphp

                        @if (empty($hiddenNumbers) || !$hiddenNumbers->contains($nomor))
                            <tr>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $order->nomor_transaksi }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <a href="{{ route('admin.pelanggan.detail', ['id' => $order->id_pel]) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ $order->data_pelanggan->nama_pel ?? '-' }}
                                    </a>
                                </td>

                                {{-- Pesanan --}}
                                <td class="px-6 py-4 text-gray-600">
                                    <ul class="list-disc ml-5 space-y-1">
                                        @foreach ($order->order_paket as $paket)
                                            @if ($paket->paket)
                                                <li>{{ $paket->paket->nama_paket }} x{{ $paket->jumlah_orderPaket }}
                                                </li>
                                            @endif
                                        @endforeach

                                        @foreach ($order->order_produk as $produk)
                                            @if ($produk->produk)
                                                <li>{{ $produk->produk->nama_produk }}
                                                    x{{ $produk->jumlah_orderProduk }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </td>

                                <td class="px-6 py-4 text-gray-800 font-semibold">
                                    Rp{{ number_format($order->total_belanjaan, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-400 text-white',
                                                'proses' => 'bg-blue-500 text-white',
                                                'dikirim' => 'bg-purple-500 text-white',
                                                'digunakan' => 'bg-red-600 text-white',
                                                'expired' => 'bg-gray-700 text-white',
                                                'selesai' => 'bg-green-500 text-white',
                                                'batal' => 'bg-gray-400 text-white',
                                            ];
                                        @endphp

                                        @foreach (array_keys($statusColors) as $status)
                                            <form
                                                action="{{ route('admin.order.update', ['id' => $order->id_order]) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_order" value="{{ $status }}">
                                                <button type="submit"
                                                    class="px-2 py-1 text-xs rounded font-semibold
                                        {{ $order->status_order === $status ? $statusColors[$status] : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                                    {{ ucfirst($status) }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layoute-admin>
