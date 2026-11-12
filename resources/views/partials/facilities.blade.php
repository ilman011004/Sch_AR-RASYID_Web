@if ($facilities->isNotEmpty())
<style>
    .fac-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.4rem; }
    .fac {
        position: relative; margin: 0; overflow: hidden; aspect-ratio: 4 / 5; border-radius: 20px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 60%, #000), var(--primary));
        box-shadow: var(--shadow-card);
    }
    .fac img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .9s var(--ease-out); }
    .fac:hover img { transform: scale(1.06); }
    .fac::after { content: ""; position: absolute; inset: 0; pointer-events: none; background: linear-gradient(to top, rgba(6, 20, 36, .88) 0%, rgba(6, 20, 36, .35) 45%, transparent 72%); }
    .fac figcaption { position: absolute; z-index: 2; left: 0; right: 0; bottom: 0; padding: 1.4rem 1.5rem; color: #fff; }
    .fac h3 { color: #fff; margin: 0 0 .3rem; font-size: 1.2rem; }
    .fac h3::before { content: ""; display: block; width: 32px; height: 3px; border-radius: 3px; margin-bottom: .7rem; background: var(--accent-2, #f5b800); }
    .fac p { margin: 0; font-size: .92rem; line-height: 1.5; color: rgba(255, 255, 255, .86); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    @media (max-width: 1000px) { .fac-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .fac-grid { grid-template-columns: 1fr; } .fac { aspect-ratio: 4 / 3; } }
</style>

<section class="section alt">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">Fasilitas</span>
            <h2>Fasilitas Penunjang Belajar</h2>
        </div>

        <div class="fac-grid" data-stagger>
            @foreach ($facilities as $f)
                <figure class="fac">
                    @if ($f->imageUrl())
                        <img src="{{ $f->imageUrl() }}" alt="{{ $f->name }}" loading="lazy">
                    @endif
                    <figcaption>
                        <h3>{{ $f->name }}</h3>
                        @if ($f->description)<p>{{ $f->description }}</p>@endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif