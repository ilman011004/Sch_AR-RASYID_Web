@extends('layouts.admin')

@section('title', $d['label'])

@section('content')
<div class="page-head">
    <h1>{{ $d['label'] }}</h1>
    <a class="btn" href="{{ route('admin.resource.create', $resource) }}">+ Tambah</a>
</div>

@php
    // Peta label untuk kolom bertipe select (mis. status, jenis).
    $maps = collect($d['fields'])->where('type', 'select')->mapWithKeys(fn ($f) => [$f['name'] => $f['options']]);
@endphp

<table class="table">
    <thead>
        <tr>
            @foreach ($d['columns'] as $label)<th>{{ $label }}</th>@endforeach
            <th class="right">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                @foreach ($d['columns'] as $col => $label)
                    @php
                        $v = $item->{$col};
                        if ($v instanceof \DateTimeInterface) { $v = $v->format('d/m/Y H:i'); }
                        elseif (isset($maps[$col])) { $v = $maps[$col][$v] ?? $v; }
                    @endphp
                    <td>{{ \Illuminate\Support\Str::limit((string) $v, 80) }}</td>
                @endforeach
                <td class="right actions">
                    <a href="{{ route('admin.resource.edit', [$resource, $item->id]) }}">Ubah</a>
                    <form method="post" action="{{ route('admin.resource.destroy', [$resource, $item->id]) }}"
                          onsubmit="return confirm('Hapus data ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="link-btn danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($d['columns']) + 1 }}" class="muted">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $items->links('partials.pager') }}
@endsection