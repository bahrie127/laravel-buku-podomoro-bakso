{{--
|--------------------------------------------------------------------------
| Purpose
|--------------------------------------------------------------------------
| File ini menjelaskan tujuan dibuatnya aplikasi "BukuBisnis".
| Diharapkan bisa menjadi referensi bagi developer maupun pengguna
| untuk memahami arah dari aplikasi ini.
--}}

@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-4">Tujuan Aplikasi BukuBisnis</h1>

    <p class="mb-4">
        Aplikasi <strong>BukuBisnis</strong> dibuat untuk membantu UMKM, freelancer,
        maupun individu dalam mencatat pemasukan dan pengeluaran sehari-hari
        secara sederhana namun terstruktur.
    </p>

    <p class="mb-4">
        Dengan adanya fitur pencatatan transaksi, pengelompokan kategori,
        transfer antar akun, serta laporan mingguan dan bulanan, pengguna
        dapat mengetahui kondisi keuangan mereka tanpa harus bergantung
        pada spreadsheet manual.
    </p>

    <p class="mb-4">
        Tujuan utama:
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li>Menyediakan solusi <em>pembukuan ringan</em> yang mudah digunakan.</li>
        <li>Membantu pengguna memahami arus kas (cashflow) secara cepat.</li>
        <li>Memberikan laporan otomatis yang dapat dipakai untuk evaluasi bisnis.</li>
        <li>Menjadi dasar pengembangan ke fitur lebih lanjut seperti grafik,
            integrasi pembayaran, maupun akuntansi lanjutan.</li>
    </ul>

    <p>
        Dengan aplikasi ini, diharapkan pengguna bisa lebih fokus pada pengembangan
        usaha mereka, sementara pencatatan keuangan tetap rapih dan mudah dipantau.
    </p>
</div>
@endsection
