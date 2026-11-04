{{-- Peta satu lokasi untuk semua jenjang. Alamat diatur di config/school.php (map). --}}
@php
    $mapQuery = config('school.map.query');
    $mapEmbed = 'https://www.google.com/maps?q='.urlencode($mapQuery).'&hl=id&z=17&output=embed';
    $mapOpen = 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapQuery);
@endphp

<div class="map-card">
    <iframe class="map" src="{{ $mapEmbed }}" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade" title="Lokasi {{ config('school.map.name') }}"></iframe>
    <div class="map-info">
        <div>
            <strong>{{ config('school.map.name') }}</strong>
            <div class="muted small">{{ config('school.map.address') }}</div>
        </div>
        <a class="btn btn-sm" href="{{ $mapOpen }}" target="_blank" rel="noopener">Buka di Google Maps</a>
    </div>
</div>