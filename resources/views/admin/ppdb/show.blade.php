@extends('layouts.admin')

@section('title', 'Pendaftar '.$r->reg_number)

@section('content')
<div class="page-head">
    <h1>{{ $r->student_name }} <small class="muted">{{ $r->reg_number }}</small></h1>
    <a href="{{ route('admin.ppdb.index') }}">← Kembali</a>
</div>

<div class="grid-2">
    <section class="panel">
        <h2>Data calon siswa</h2>
        <dl class="dl">
            <dt>Nama</dt><dd>{{ $r->student_name }}</dd>
            <dt>NIK</dt><dd>{{ $r->nik ?: '—' }}</dd>
            <dt>Jenis kelamin</dt><dd>{{ $r->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
            <dt>Tempat, tgl lahir</dt><dd>{{ $r->birth_place }}, {{ $r->birth_date->translatedFormat('d F Y') }}</dd>
            <dt>Agama</dt><dd>{{ $r->religion ?: '—' }}</dd>
            <dt>Alamat</dt><dd>{{ $r->address }}</dd>
            <dt>Asal sekolah</dt><dd>{{ $r->previous_school ?: '—' }}</dd>
        </dl>

        <h2>Orang tua / wali</h2>
        <dl class="dl">
            <dt>Nama</dt><dd>{{ $r->parent_name }}</dd>
            <dt>No. HP</dt>
            <dd>{{ $r->parent_phone }}
                @php $wa = preg_replace('/\D/', '', $r->parent_phone); @endphp
                @if (strlen($wa) >= 9)
                    · <a href="https://wa.me/{{ str_starts_with($wa, '0') ? '62'.substr($wa, 1) : $wa }}" target="_blank" rel="noopener">WhatsApp</a>
                @endif
            </dd>
            <dt>Email</dt><dd>{{ $r->parent_email ?: '—' }}</dd>
            <dt>Pekerjaan</dt><dd>{{ $r->parent_job ?: '—' }}</dd>
        </dl>

        <h2>Dokumen</h2>
        <ul class="plain">
            @foreach ($docs as $field => $label)
                <li>
                    {{ $label }}:
                    @if ($r->{$field})
                        <a href="{{ route('admin.ppdb.document', [$r->id, $field]) }}" target="_blank" rel="noopener">Lihat berkas</a>
                    @else
                        <span class="muted">tidak diunggah</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <section class="panel">
        <h2>Proses pendaftaran</h2>
        <p class="muted small">Didaftarkan {{ $r->created_at->translatedFormat('d F Y H:i') }}</p>

        <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
            @csrf @method('PUT')

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $r->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="admin_note">Catatan untuk pendaftar</label>
                <textarea id="admin_note" name="admin_note" rows="4">{{ old('admin_note', $r->admin_note) }}</textarea>
                <small>Catatan ini ikut tampil ke pendaftar saat mereka mengecek status.</small>
            </div>

            <button class="btn" type="submit">Simpan</button>
        </form>

        <hr>
        <form method="post" action="{{ route('admin.ppdb.destroy', $r->id) }}"
              onsubmit="return confirm('Hapus data pendaftar ini beserta dokumennya? Tindakan ini tidak bisa dibatalkan.')">
            @csrf @method('DELETE')
            <button type="submit" class="link-btn danger">Hapus data pendaftar</button>
        </form>
    </section>
</div>
@endsection