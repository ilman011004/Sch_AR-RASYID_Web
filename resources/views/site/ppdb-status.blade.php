@extends('layouts.site')

@section('title', 'Cek Status Pendaftaran — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Cek Status Pendaftaran</h1></div>
</section>

<section class="section">
    <div class="wrap narrow">
        <form method="post" action="{{ route('ppdb.check', $school) }}" class="form">
            @csrf
            <label>Nomor pendaftaran
                <input type="text" name="reg_number" value="{{ old('reg_number') }}" required maxlength="50" placeholder="PPDB-2026-SD-0001">
                @error('reg_number')<span class="err">{{ $message }}</span>@enderror
            </label>
            <label>Tanggal lahir calon siswa
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" required>
                @error('birth_date')<span class="err">{{ $message }}</span>@enderror
            </label>
            <button class="btn" type="submit">Cek</button>
        </form>

        @if (! empty($searched))
            @if ($result)
                <div class="result">
                    <h3>{{ $result->student_name }}</h3>
                    <p class="muted small">{{ $result->reg_number }}</p>
                    <p>Status: <span class="tag {{ $result->status === 'diterima' ? 'tag-ok' : ($result->status === 'ditolak' ? 'tag-warn' : '') }}">{{ $result->statusLabel() }}</span></p>
                    @if ($result->admin_note)
                        <p><strong>Catatan sekolah:</strong><br>{{ $result->admin_note }}</p>
                    @endif
                </div>
            @else
                <div class="alert err-box">Data tidak ditemukan. Periksa kembali nomor pendaftaran dan tanggal lahir.</div>
            @endif
        @endif
    </div>
</section>
@endsection