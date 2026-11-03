{{-- Kartu orang: foto persegi, nama, lalu jabatan. Variabel: $name, $role, $photo --}}
<div class="person">
    @if (! empty($photo))
        <img src="{{ asset('storage/'.$photo) }}" alt="{{ $name }}" loading="lazy">
    @else
        <div class="avatar" aria-hidden="true">{{ mb_substr((string) $name ?: '?', 0, 1) }}</div>
    @endif
    <strong>{{ $name ?: '—' }}</strong>
    @if (! empty($role))
        <span class="muted small">{{ $role }}</span>
    @endif
</div>