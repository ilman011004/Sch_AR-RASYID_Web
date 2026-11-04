{{-- Variabel: $entity (sekolah atau yayasan), $contactUrl (opsional) --}}
@php
    $addr = $entity->address ?: config('school.map.address');
    // Jam operasional: pakai isian di Pengaturan Sekolah bila ada, kalau tidak pakai config/school.php
    $hours = \App\Models\School::lines($entity->operating_hours ?: config('school.hours'));
    $mapQuery = config('school.map.query');
    $mapEmbed = 'https://www.google.com/maps?q='.urlencode($mapQuery).'&hl=id&z=17&output=embed';

    if ($entity->whatsapp) {
        $cta = 'https://wa.me/'.$entity->whatsapp;
        $ctaBlank = true;
    } elseif ($entity->email) {
        $cta = 'mailto:'.$entity->email;
        $ctaBlank = false;
    } elseif ($entity->phone) {
        $cta = 'tel:'.preg_replace('/[^0-9+]/', '', $entity->phone);
        $ctaBlank = false;
    } else {
        $cta = $contactUrl ?? '#';
        $ctaBlank = false;
    }
@endphp

<style>
    .loc { font-family: var(--font-sans); color: #fff; padding: 4.5rem 0; background:
        radial-gradient(700px 360px at 8% 0%, rgba(255, 255, 255, .07), transparent 60%),
        linear-gradient(135deg, color-mix(in srgb, var(--primary) 48%, #000), color-mix(in srgb, var(--primary) 66%, #000)); }
    .loc-grid { display: grid; align-items: center; gap: 3.5rem; grid-template-columns: 1fr 1.25fr; }
    .loc-kicker { display: block; text-transform: uppercase; letter-spacing: .16em; font-size: .8rem; font-weight: 700; color: rgba(255, 255, 255, .85); margin-bottom: .6rem; }
    .loc h2 { font-family: inherit; color: #fff; font-weight: 800; font-size: clamp(1.8rem, 3.6vw, 2.6rem); line-height: 1.15; letter-spacing: -.01em; margin: 0 0 1.8rem; }
    .loc h2 em { font-style: normal; font-weight: 800; color: color-mix(in srgb, var(--primary) 35%, #bbf7d0); }
    .loc-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 1.3rem; }
    .loc-list li { display: flex; gap: 1rem; align-items: flex-start; }
    .loc-ic { flex: none; width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; color: #bbf7d0; background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .12); }
    .loc-ic svg { width: 24px; height: 24px; }
    .loc-list small { display: block; text-transform: uppercase; letter-spacing: .12em; font-size: .72rem; font-weight: 700; color: rgba(255, 255, 255, .62); margin-bottom: .15rem; }
    .loc-list div > span, .loc-list div > a { display: block; color: #fff; text-decoration: none; line-height: 1.6; font-size: 1.02rem; font-weight: 500; }
    .loc-list a:hover { text-decoration: underline; }
    .loc-btn { display: inline-flex; align-items: center; gap: .6rem; margin-top: 2rem; padding: .95rem 1.8rem; border-radius: 999px; background: #d97706; color: #fff; font-family: inherit; font-weight: 700; text-decoration: none; box-shadow: 0 10px 24px rgba(0, 0, 0, .25); transition: transform .15s, filter .15s; }
    .loc-btn:hover { transform: translateY(-2px); filter: brightness(1.08); color: #fff; }
    .loc-btn svg { width: 20px; height: 20px; }
    .loc-map { overflow: hidden; border-radius: 26px; border: 5px solid rgba(255, 255, 255, .14); box-shadow: 0 24px 60px rgba(0, 0, 0, .35); background: #fff; }
    .loc-map iframe { display: block; width: 100%; height: 440px; border: 0; }
    @media (max-width: 900px) {
        .loc { padding: 3.2rem 0; }
        .loc-grid { grid-template-columns: 1fr; gap: 2.2rem; }
        .loc-map iframe { height: 340px; }
    }
</style>

<section class="loc">
    <div class="wrap loc-grid">
        <div>
            <span class="loc-kicker">Lokasi Kami</span>
            <h2>Hubungi <em>Kami</em></h2>

            <ul class="loc-list">
                <li>
                    <span class="loc-ic">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
                    </span>
                    <div>
                        <small>Alamat</small>
                        <span>{!! nl2br(e($addr)) !!}</span>
                    </div>
                </li>

                @if ($entity->phone)
                <li>
                    <span class="loc-ic">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
                    </span>
                    <div>
                        <small>Telepon</small>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $entity->phone) }}">{{ $entity->phone }}</a>
                    </div>
                </li>
                @endif

                @if ($entity->email)
                <li>
                    <span class="loc-ic">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18a1 1 0 0 1 1 1v.5l-10 6.25L2 6.5V6a1 1 0 0 1 1-1Zm19 3.85V18a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8.85l10 6.25 10-6.25Z"/></svg>
                    </span>
                    <div>
                        <small>Email</small>
                        <a href="mailto:{{ $entity->email }}">{{ $entity->email }}</a>
                    </div>
                </li>
                @endif

                @if ($hours)
                <li>
                    <span class="loc-ic">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 5v5.2l3.6 2.1-.8 1.3L11 13V7h2Z"/></svg>
                    </span>
                    <div>
                        <small>Jam Operasional</small>
                        @foreach ($hours as $h)
                            <span>{{ $h }}</span>
                        @endforeach
                    </div>
                </li>
                @endif
            </ul>

            <a class="loc-btn" href="{{ $cta }}" @if ($ctaBlank) target="_blank" rel="noopener" @endif>
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18a1 1 0 0 1 1 1v.5l-10 6.25L2 6.5V6a1 1 0 0 1 1-1Zm19 3.85V18a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8.85l10 6.25 10-6.25Z"/></svg>
                Hubungi Kami Sekarang
            </a>
        </div>

        <div class="loc-map">
            <iframe src="{{ $mapEmbed }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi {{ config('school.map.name') }}"></iframe>
        </div>
    </div>
</section>