{{-- Variabel: $entity (sekolah atau yayasan), $goals (opsional, true = tampilkan tujuan) --}}
@php
    $missions = $entity->missionList();
    $goalList = ! empty($goals) ? $entity->goalsList() : [];
@endphp

<div class="vm-grid">
    <div class="vm-visi">
        <span class="vm-label">Visi</span>
        <p>{{ $entity->vision ?: 'Visi belum diisi.' }}</p>
    </div>

    <div class="vm-misi">
        <span class="kicker">Misi</span>
        @if ($missions)
            <ol class="steps">
                @foreach ($missions as $m)
                    <li>{{ $m }}</li>
                @endforeach
            </ol>
        @else
            <p class="muted">Misi belum diisi.</p>
        @endif
    </div>
</div>

@if (! empty($goals))
    <div class="vm-goals">
        <span class="kicker">Tujuan</span>
        @if ($goalList)
            <ol class="steps">
                @foreach ($goalList as $g)
                    <li>{{ $g }}</li>
                @endforeach
            </ol>
        @else
            <p class="muted">Tujuan belum diisi.</p>
        @endif
    </div>
@endif