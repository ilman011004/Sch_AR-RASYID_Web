@extends('layouts.site')

@section('title', 'Pendaftaran Berhasil — '.$school->name)

@section('content')
<section class="section">
    <div class="wrap narrow center">
        <div class="icon big">✅</div>
        <h1>Pendaftaran berhasil dikirim</h1>
        <p>Terima kasih. Data atas nama <strong>{{ $reg['name'] }}</strong> sudah kami terima.</p>

        <div class="reg-box">
            <div class="muted small">Nomor pendaftaran Anda</div>
            <div class="reg-number">{{ $reg['number'] }}</div>
        </div>

        <p>Catat nomor ini. Untuk melihat status pendaftaran, gunakan nomor tersebut bersama tanggal lahir calon siswa.</p>

        <p>
            <a class="btn" href="{{ route('ppdb.status', $school) }}">Cek status pendaftaran</a>
            <a class="btn btn-outline" href="{{ route('home', $school) }}">Kembali ke beranda</a>
        </p>
    </div>
</section>
@endsection