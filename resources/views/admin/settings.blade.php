@extends('layouts.admin')

@section('title', 'Pengaturan Sekolah')

@section('content')
<div class="page-head">
    <h1>Pengaturan — {{ $school->name }}</h1>
</div>

<form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    @foreach ($groups as $title => $fields)
        <fieldset class="panel">
            <legend>{{ $title }}</legend>
            @include('admin._fields', ['fields' => $fields, 'model' => $school])
        </fieldset>
    @endforeach

    <button type="submit" class="btn">Simpan semua</button>
</form>
@endsection