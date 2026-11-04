{{-- Variabel: $entity (sekolah atau yayasan). Foto kepala sekolah + sambutan. --}}
@if ($entity->head_name || $entity->welcome_message)
<section class="section">
    <div class="wrap welcome">
        <div class="welcome-photo">
            @if ($entity->headPhotoUrl())
                <img src="{{ $entity->headPhotoUrl() }}" alt="{{ $entity->head_title }} {{ $entity->head_name }}">
            @else
                <div class="avatar" aria-hidden="true">{{ mb_substr((string) ($entity->head_name ?: $entity->name), 0, 1) }}</div>
            @endif
        </div>

        <div class="welcome-body">
            <span class="kicker">Sambutan</span>
            <h2>Sambutan {{ $entity->head_title }}</h2>
            @if ($entity->welcome_message)
                <div class="msg">{!! nl2br(e($entity->welcome_message)) !!}</div>
            @endif
            <div class="welcome-sign">
                <strong>{{ $entity->head_name }}</strong>
                <span class="muted">{{ $entity->head_title }}</span>
            </div>
        </div>
    </div>
</section>
@endif