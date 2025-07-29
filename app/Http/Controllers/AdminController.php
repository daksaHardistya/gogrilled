<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Models\data_pelanggan;
use App\Models\menu_paket;
use App\Models\produk_satuan;
use App\Models\tabel_order;
use App\Models\tabel_orderPaket;
use App\Models\tabel_orderProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function orderShow(Request $request)
    {
        $query = tabel_order::with(['data_pelanggan', 'order_paket.paket', 'order_produk.produk'])->orderBy('created_at', 'desc');

        // Filter berdasarkan bulan
        if ($request->filled('bulan')) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $request->bulan);
                $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
            } catch (\Exception $e) {
                // abaikan jika format tidak valid
            }
        }

        // Filter berdasarkan pencarian nama atau nomor telepon
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas('data_pelanggan', function ($q) use ($cari) {
                $q->where('nama_pel', 'like', "%$cari%")->orWhere('nomor_tlp', 'like', "%$cari%");
            });
        }

        // Ambil semua order dulu
        $orders = $query->get();

        $hiddenNumbers = collect();

        if ($request->filled('inaktif')) {
            $bulan = (int) $request->inaktif;

            if ($bulan >= 1 && $bulan <= 6) {
                $batasWaktu = now()->subMonths($bulan);

                // Ambil id pelanggan yang memiliki order dalam X bulan terakhir (harus disembunyikan)
                $recentActive = \App\Models\tabel_order::where('created_at', '>=', $batasWaktu)->pluck('id_pel')->unique();

                // Pelanggan yang tidak ada di daftar recentActive = yang muncul
                $hiddenNumbers = \App\Models\data_pelanggan::whereIn('id_pel', $recentActive)->pluck('nomor_tlp');
            }
        }

        return view('admin.order', compact('orders', 'hiddenNumbers'));
    }

    public function dashboard()
    {
        $orders = tabel_order::with('data_pelanggan')->orderBy('created_at', 'desc')->get();

        $totalOrders = $orders->count();
        $stokPaket = menu_paket::sum('stock_paket');
        $stokProduk = produk_satuan::sum('stock_produk');

        // Hitung total pendapatan hari ini
        $today = Carbon::now()->format('Y-m-d');
        $totalPendapatan = tabel_order::whereDate('created_at', $today)->where('status_order', 'digunakan')->sum('total_belanjaan');

        // Hitung total pendapatan bulan ini
        $currentMonth = Carbon::now()->format('m');
        $totalPendapatanBulanIni = tabel_order::whereMonth('created_at', $currentMonth)->where('status_order', 'digunakan')->sum('total_belanjaan');
        $statusCounts = [
            'pending' => $orders->where('status_order', 'pending')->count(),
            'proses' => $orders->where('status_order', 'proses')->count(),
            'dikirim' => $orders->where('status_order', 'dikirim')->count(),
            'booked' => $orders->where('status_order', 'booked')->count(),
            'expired' => $orders->where('status_order', 'expired')->count(),
            'selesai' => $orders->where('status_order', 'selesai')->count(),
            'batal' => $orders->where('status_order', 'batal')->count(),
        ];
        return view('admin.dashboard', compact('orders', 'totalOrders', 'stokPaket', 'stokProduk', 'totalPendapatanBulanIni', 'statusCounts'));

        // return view('admin.dashboard', compact('orders', 'totalOrders', 'stokPaket', 'stokProduk', 'totalPendapatan', 'totalPendapatanBulanIni'));
    }

    public function orderUpdate(Request $request, $id)
    {
        $order = tabel_order::findOrFail($id);
        $newStatus = $request->status_order; // Status baru dari request

        // Jika status baru adalah 'batal', hapus order
        if (strtolower($newStatus) === 'batal') {
            try {
                // Opsional: Hapus order_produk dan order_paket terkait secara manual
                // Jika Anda menggunakan onDelete('cascade') di migrasi, ini tidak diperlukan.
                // tabel_orderProduk::where('id_order', $id)->delete();
                // tabel_orderPaket::where('id_order', $id)->delete();

                $order->delete();
                return redirect()->back()->with('success', 'Order berhasil dibatalkan dan dihapus.');
            } catch (\Exception $e) {
                Log::error("Failed to delete order $id: " . $e->getMessage());
                return redirect()->back()->with('error', 'Gagal menghapus order saat membatalkan.');
            }
        }

        // Jika status bukan 'batal', lanjutkan dengan update status dan pengiriman pesan
        $order->status_order = $newStatus;
        $order->save();

        // Tidak mengirim pesan jika status pending (seperti logika yang sudah ada)
        if (strtolower($newStatus) === 'pending') {
            return redirect()->back()->with('success', 'Status order berhasil diperbarui.');
        }

        // Ambil data orderPaket dan orderProduk setelah order disimpan
        $orderPaket = tabel_orderPaket::where('id_order', $id)->get();
        $orderProduk = tabel_orderProduk::where('id_order', $id)->get();

        $pelanggan = $order->data_pelanggan;
        // Pastikan $pelanggan tidak null sebelum mengakses propertinya
        if (!$pelanggan) {
            return redirect()->back()->with('error', 'Data pelanggan tidak ditemukan untuk order ini.');
        }
        $nomor = preg_replace('/[^0-9]/', '', $pelanggan->nomor_tlp);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        // Format isi pesan berdasarkan status
        $statusText = match (strtolower($newStatus)) {
            'dikirim' => 'telah *dikirim* dan sedang dalam perjalanan',
            'digunakan' => 'telah *diterima* dan sedang digunakan',
            'proses' => 'sedang *diproses*. Mohon ditunggu sebentar lagi.',
            'selesai' => 'telah *selesai*',
            'expired' => 'telah memasuki waktu *pengembalian* alat. Silakan hubungi admin untuk melakukan pengembalian.',
            default => 'telah diperbarui',
        };

        // Format pesan WhatsApp
        $pesan = "Halo *{$pelanggan->nama_pel}*,\n\n" . "Status pesanan Anda telah diperbarui:\n\n" . '- Paket: *' . ($orderPaket->pluck('paket.nama_paket')->implode(', ') ?: '-') . "*\n" . '- Jumlah Paket: *' . ($orderPaket->pluck('jumlah_orderPaket')->implode(', ') ?: '-') . "*\n" . '- Produk: *' . ($orderProduk->pluck('produk.nama_produk')->implode(', ') ?: '-') . "*\n" . '- Jumlah Produk: *' . ($orderProduk->pluck('jumlah_orderProduk')->implode(', ') ?: '-') . "*\n" . '- Total Belanjaan: *Rp ' . number_format($order->total_belanjaan, 0, ',', '.') . "*\n\n" . "Pesanan Anda saat ini $statusText\n\n" . 'Terima kasih telah mempercayakan harimu bersama *Go.Grilled*!';

        // Kirim pesan via WhatsApp
        kirimPesanWA($nomor, $pesan);

        return redirect()->back()->with('success', 'Status order berhasil diperbarui.');
    }

    public function pelangganDetail($id)
    {
        $pelanggan = data_pelanggan::findOrFail($id);
        return view('admin.pelanggan', compact('pelanggan'));
    }

    // CONTROL ADMIN PRODUK
    public function produkShow()
    {
        $produkList = produk_satuan::all();
        return view('admin.produk', compact('produkList'));
    }
    public function produkCreate()
    {
        return view('admin.produk.create');
    }
    // Simpan Produk
    public function produkStore(Request $request)
    {
        $data = $request->validate([
            'kode_produk' => 'required|unique:tabel_produk,kode_produk',
            'nama_produk' => 'required',
            'harga_produk' => 'required|numeric',
            'stock_produk' => 'required|integer',
            'image_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5128', // Max 5MB
        ]);

        if ($request->hasFile('image_produk')) {
            $data['image_produk'] = $request->file('image_produk')->store('produk_satuan', 'public');
        }

        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();

        produk_satuan::create($data);

        return redirect()->route('admin.produk.show')->with('success', 'Produk berhasil ditambahkan');
    }
    // Form Edit
    public function produkEdit($id)
    {
        $produk = produk_satuan::findOrFail($id);
        return view('admin.produk.edit', compact('produk'));
    }
    // Update Produk
    public function produkUpdate(Request $request, $id)
    {
        $produk = produk_satuan::findOrFail($id);
        $data = $request->validate([
            'nama_produk' => 'required',
            'harga_produk' => 'required|numeric',
            'stock_produk' => 'required|integer',
            'image_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5128', // Max 5MB
        ]);

        if ($request->hasFile('image_produk')) {
            // Hapus gambar lama jika ada
            if ($produk->image_produk && Storage::disk('public')->exists($produk->image_produk)) {
                Storage::disk('public')->delete($produk->image_produk);
            }
            $data['image_produk'] = $request->file('image_produk')->store('produk_satuan', 'public');
        } else {
            // Jika tidak ada file baru yang diunggah, pastikan image_produk tidak dihapus dari $data
            // agar tidak menimpa dengan null jika kolomnya tidak nullable
            unset($data['image_produk']);
        }

        $produk->update($data);
        return redirect()->route('admin.produk.show')->with('success', 'Produk berhasil diperbarui');
    }
    // Hapus Produk
    public function produkDelete($id)
    {
        $produk = produk_satuan::findOrFail($id);
        // Hapus gambar jika ada
        if ($produk->image_produk && Storage::disk('public')->exists($produk->image_produk)) {
            Storage::disk('public')->delete($produk->image_produk);
        }
        $produk->delete();
        return redirect()->route('admin.produk.show')->with('success', 'Produk berhasil dihapus');
    }
    public function updateStockProduk(Request $request, $id)
    {
        $produk = produk_satuan::findOrFail($id);
        $produk->stock_produk = $request->stock_produk;
        $produk->save();

        return redirect()->back()->with('success', 'Stok produk berhasil diperbarui.');
    }

    // CONTROL UNTUK ADMIN PAKET
public function paketShow()
{
    $paketList = menu_paket::orderByRaw("
        CASE 
            WHEN kategori_paket = 'Basic' THEN 1
            WHEN kategori_paket = 'Special' THEN 2
            WHEN kategori_paket = 'Family' THEN 3
            ELSE 4
        END
    ")->orderByRaw("CAST(REGEXP_SUBSTR(nama_paket, '[0-9]+') AS UNSIGNED)")
      ->get();

    return view('admin.paket', compact('paketList'));
}


    public function paketCreate()
    {
        return view('admin.paket.create');
    }

    // Simpan Paket
    public function paketStore(Request $request)
    {
        $data = $request->validate([
            'kode_paket' => 'required|unique:tabel_paket,kode_paket',
            'nama_paket' => 'required',
            'detail_paket' => 'required',
            'kategori_paket' => 'required',
            'harga_paket' => 'required|numeric',
            'stock_paket' => 'required|integer',
            'image_paket' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5128', // Max 5MB
        ]);

        if ($request->hasFile('image_paket')) {
            $data['image_paket'] = $request->file('image_paket')->store('menu_paket', 'public');
        }

        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();

        menu_paket::create($data); // Pastikan kolom 'image_paket' di model menu_paket sudah ada di $fillable

        return redirect()->route('admin.paket.show')->with('success', 'Paket berhasil ditambahkan');
    }

    // Form Edit
    public function paketEdit($id)
    {
        $paket = menu_paket::findOrFail($id);
        return view('admin.paket.edit', compact('paket'));
    }

    // Update Paket
    public function paketUpdate(Request $request, $id)
    {
        $paket = menu_paket::findOrFail($id);
        $data = $request->validate([
            'nama_paket' => 'required',
            'detail_paket' => 'required',
            'kategori_paket' => 'required',
            'harga_paket' => 'required|numeric',
            'stock_paket' => 'required|integer',
            'image_paket' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5128', // Max 5MB
        ]);

        if ($request->hasFile('image_paket')) {
            // Hapus gambar lama jika ada
            if ($paket->image_paket && Storage::disk('public')->exists($paket->image_paket)) {
                Storage::disk('public')->delete($paket->image_paket);
            }

            $data['image_paket'] = $request->file('image_paket')->store('menu_paket', 'public');
        } else {
            // Jika tidak ada file baru yang diunggah, pastikan image_paket tidak dihapus dari $data
            // agar tidak menimpa dengan null jika kolomnya tidak nullable
            unset($data['image_paket']);
        }

        $paket->update($data);
        return redirect()->route('admin.paket.show')->with('success', 'Paket berhasil diperbarui');
    }

    // Hapus Paket
    public function paketDelete($id)
    {
        $paket = menu_paket::findOrFail($id);

        // Hapus gambar jika ada
        if ($paket->image_paket && Storage::disk('public')->exists($paket->image_paket)) {
            Storage::disk('public')->delete($paket->image_paket);
        }

        $paket->delete();
        return redirect()->route('admin.paket.show')->with('success', 'Paket berhasil dihapus');
    }

    // Update stok paket secara langsung
    public function updateStockPaket(Request $request, $id)
    {
        $paket = menu_paket::findOrFail($id);
        $paket->stock_paket = $request->stock_paket;
        $paket->save();

        return redirect()->back()->with('success', 'Stok paket berhasil diperbarui.');
    }

    // CONTROL UNTUK ADMIN PEMBUKUAN
    public function pembukuanShow(Request $request)
    {
        $query = tabel_order::with('data_pelanggan')->whereNotIn('status_order', ['batal', 'pending']);

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $tanggal_awal = $request->tanggal_awal;
            $tanggal_akhir = $request->tanggal_akhir;
            $query->whereBetween('created_at', [$tanggal_awal, $tanggal_akhir]);
        }

        // Urutkan berdasarkan tanggal terbaru
        $orders = $query->orderBy('created_at', 'desc')->get();

        $transferOrders = $orders->where('tipe_pembayaran', 'Transfer Bank');
        $cashOrders = $orders->where('tipe_pembayaran', 'Cash');

        $totalTransfer = $transferOrders->sum('total_belanjaan');
        $totalCash = $cashOrders->sum('total_belanjaan');
        $totalSemua = $totalTransfer + $totalCash;

        return view('admin.pembukuan', compact('transferOrders', 'cashOrders', 'totalTransfer', 'totalCash', 'totalSemua'));
    }

    // public function uploadBuktiTf(Request $request)
    // {
    //     // Tambahkan validasi di sini
    //     $request->validate([
    //         'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Max 5MB (2048KB)
    //     ]);

    //     if ($request->hasFile('bukti_pembayaran')) {
    //         $file = $request->file('bukti_pembayaran');
    //         $fileName = time() . '_' . $file->getClientOriginalName();
    //         $path = $file->storeAs('bukti_transfer', $fileName, 'public');

    //         return response()->json([
    //             'success' => true,
    //             'fileName' => $fileName,
    //             'filePath' => Storage::url($path), // Opsional: kirim juga URL yang bisa diakses publik
    //         ]);
    //     }

    //     return response()->json(['success' => false, 'message' => 'File tidak ditemukan atau terjadi kesalahan'], 400);
    // }
}

// Pastikan fungsi ini didefinisikan di luar class Controller jika tidak menjadi method public/private di class
// Atau masukkan ke dalam class Helper atau Service Provider
function kirimPesanWA($nomor, $pesan)
{
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->request('POST', 'https://api.fonnte.com/send', [
            'headers' => [
                'Authorization' => 'cFh96YKghJi8GQkN3LFN', // ganti dengan API key Fonnte kamu
            ],
            'form_params' => [
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62', // Opsional, default 62 untuk Indonesia
            ],
        ]);
        return json_decode($response->getBody(), true);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Tangani kesalahan jika pengiriman pesan WA gagal
        Log::error('Failed to send WhatsApp message: ' . $e->getMessage());
        // Anda bisa mengembalikan false atau melempar exception lagi
        return false;
    }
}
