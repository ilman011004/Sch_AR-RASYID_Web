@extends('layouts.admin')

@section('title', ($item ? 'Ubah ' : 'Tambah ').$d['label'])

@section('content')
<div class="page-head">
    <h1>{{ $item ? 'Ubah' : 'Tambah' }} — {{ $d['label'] }}</h1>
    <a href="{{ route('admin.resource.index', $resource) }}">← Kembali</a>
</div>

<form method="post" enctype="multipart/form-data" class="panel"
      action="{{ $item ? route('admin.resource.update', [$resource, $item->id]) : route('admin.resource.store', $resource) }}">
    @csrf
    @if ($item) @method('PUT') @endif

    @include('admin._fields', ['fields' => $d['fields'], 'model' => $item])

    <button type="submit" class="btn">Simpan</button>
</form>
@endsection