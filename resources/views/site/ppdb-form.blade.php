@extends('layouts.site')

@section('title', 'Formulir Pendaftaran — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <h1>Formulir Pendaftaran {{ $school->ppdb_year ?: '' }}</h1>
        <p>Masuk sebagai {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    </div>
</section>

<section class="section">
    <div class="wrap narrow">

        @if ($errors->any())
            <div class="alert err-box">Ada isian yang belum benar. Silakan periksa kolom yang ditandai.</div>
        @endif

        <form method="post" action="{{ route('ppdb.store', $school) }}" enctype="multipart/form-data" class="form">
            @csrf
            <div class="hp" aria-hidden="true">
                <label>Jangan diisi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <fieldset>
                <legend>Data calon siswa</legend>

                <label>Nama lengkap *
                    <input type="text" name="student_name" value="{{ old('student_name') }}" required maxlength="150">
                    @error('student_name')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>NIK (16 digit)
                    <input type="text" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16" pattern="[0-9]{16}">
                    @error('nik')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Jenis kelamin *
                    <select name="gender" required>
                        <option value="">— pilih —</option>
                        <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                    </select>
                    @error('gender')<span class="err">{{ $message }}</span>@enderror
                </label>
                <div class="row">
                    <label>Tempat lahir *
                        <input type="text" name="birth_place" value="{{ old('birth_place') }}" required maxlength="100">
                        @error('birth_place')<span class="err">{{ $message }}</span>@enderror
                    </label>
                    <label>Tanggal lahir *
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" required max="{{ now()->subDay()->toDateString() }}">
                        @error('birth_date')<span class="err">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label>Agama
                    <input type="text" name="religion" value="{{ old('religion') }}" maxlength="30">
                    @error('religion')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Alamat *
                    <textarea name="address" rows="3" required maxlength="500">{{ old('address') }}</textarea>
                    @error('address')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Asal sekolah / TK
                    <input type="text" name="previous_school" value="{{ old('previous_school') }}" maxlength="150">
                    @error('previous_school')<span class="err">{{ $message }}</span>@enderror
                </label>
            </fieldset>

            <fieldset>
                <legend>Data orang tua / wali</legend>

                <label>Nama orang tua / wali *
                    <input type="text" name="parent_name" value="{{ old('parent_name', auth()->user()->name) }}" required maxlength="150">
                    @error('parent_name')<span class="err">{{ $message }}</span>@enderror
                </label>
                <div class="row">
                    <label>Nomor HP / WhatsApp *
                        <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" required maxlength="20" inputmode="tel">
                        @error('parent_phone')<span class="err">{{ $message }}</span>@enderror
                    </label>
                    <label>Email
                        <input type="email" name="parent_email" value="{{ old('parent_email', auth()->user()->email) }}" maxlength="150">
                        @error('parent_email')<span class="err">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label>Pekerjaan
                    <input type="text" name="parent_job" value="{{ old('parent_job') }}" maxlength="100">
                    @error('parent_job')<span class="err">{{ $message }}</span>@enderror
                </label>
            </fieldset>

            <fieldset>
                <legend>Dokumen (JPG, PNG, atau PDF — maks. 2 MB per berkas)</legend>

                <label>Akta kelahiran
                    <input type="file" name="doc_birth_cert" accept=".jpg,.jpeg,.png,.pdf">
                    @error('doc_birth_cert')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Kartu keluarga
                    <input type="file" name="doc_family_card" accept=".jpg,.jpeg,.png,.pdf">
                    @error('doc_family_card')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Pas foto (JPG / PNG)
                    <input type="file" name="doc_photo" accept=".jpg,.jpeg,.png">
                    @error('doc_photo')<span class="err">{{ $message }}</span>@enderror
                </label>
            </fieldset>

            @error('website')<span class="err">{{ $message }}</span>@enderror

            <p class="muted small">Data yang Anda kirim hanya dipakai untuk proses penerimaan siswa baru {{ $school->name }}.</p>
            <div class="btns" style="margin-top:0">
                <button class="btn" type="submit">Kirim Pendaftaran</button>
                <a class="btn btn-outline" href="{{ route('ppdb', $school) }}">Kembali</a>
            </div>
        </form>
    </div>
</section>
@endsection