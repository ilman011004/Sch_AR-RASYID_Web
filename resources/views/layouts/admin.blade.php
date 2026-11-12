@php
    $me = auth()->user();
    $currentSchool = \App\Support\AdminContext::school();
    $isSuper = $me->isSuperAdmin();
    $canManage = in_array($me->role, ['super_admin', 'admin'], true);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel Admin') — Admin</title>
    @vite('resources/css/admin.css')
</head>
<body class="admin">

<aside class="sidebar">
    <div class="side-brand">Panel Admin</div>

    @if ($isSuper)
        <form method="post" action="{{ route('admin.switch-school') }}" class="school-switch">
            @csrf
            <label>Sekolah yang dikelola
                <select name="school_id" onchange="this.form.submit()">
                    @foreach (\App\Models\School::orderBy('sort')->orderBy('id')->get() as $s)
                        <option value="{{ $s->id }}" @selected($s->id === $currentSchool->id)>
                            {{ $s->name }}{{ $s->is_active || $s->isFoundation() ? '' : ' (nonaktif)' }}
                        </option>
                    @endforeach
                </select>
            </label>
        </form>
    @else
        <div class="school-switch"><small>Sekolah</small><strong>{{ $currentSchool->name }}</strong></div>
    @endif

    <nav>
        <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>Ringkasan</a>

        @foreach (config('admin.resources') as $key => $r)
            @if (in_array($me->role, $r['roles'], true))
                <a href="{{ route('admin.resource.index', $key) }}"
                   @class(['active' => request()->route('resource') === $key])>{{ $r['label'] }}</a>
            @endif
        @endforeach

        @if ($canManage && ! $isSuper)
            <a href="{{ route('admin.ppdb.index') }}" @class(['active' => request()->routeIs('admin.ppdb.index') || (! $isSuper && request()->routeIs('admin.ppdb.show'))])>Pendaftar PPDB</a>
        @endif

        @if ($isSuper)
            <a href="{{ route('admin.ppdb.all') }}" @class(['active' => request()->routeIs('admin.ppdb.all*') || ($isSuper && request()->routeIs('admin.ppdb.show'))])>Semua Pendaftar (Yayasan)</a>
        @endif

        @if ($canManage)
            <a href="{{ route('admin.messages.index') }}" @class(['active' => request()->routeIs('admin.messages.*')])>Pesan Masuk</a>
            <a href="{{ route('admin.settings') }}" @class(['active' => request()->routeIs('admin.settings*')])>Pengaturan Sekolah</a>
            <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>Akun Pengguna</a>
        @endif

        @if ($isSuper)
            <a href="{{ route('admin.schools.index') }}" @class(['active' => request()->routeIs('admin.schools.*')])>Daftar Sekolah</a>
        @endif
    </nav>

    <div class="side-foot">
        @if ($currentSchool->isFoundation())
            <a href="{{ route('portal') }}" target="_blank" rel="noopener">Lihat situs yayasan ↗</a>
        @elseif ($currentSchool->is_active)
            <a href="{{ route('home', $currentSchool) }}" target="_blank" rel="noopener">Lihat situs ↗</a>
        @endif
        <div class="who">{{ $me->name }}<br><small>{{ $me->roleLabel() }}</small></div>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="link-btn">Keluar</button>
        </form>
    </div>
</aside>

<main class="content">
    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash bad">
            <strong>Periksa kembali isian Anda:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

{{-- Pengaturan Sekolah: sembunyikan field yang tidak relevan untuk sekolah yang sedang dikelola.
     Yayasan: tanpa video profil. Jenjang sekolah: tanpa "Tentang Yayasan". --}}
@php
    $hiddenFields = $currentSchool->isFoundation()
        ? ['video_url']
        : ['about_photo', 'about_text'];
@endphp
@if (request()->routeIs('admin.settings*'))
<script>
    (function () {
        @json($hiddenFields).forEach(function (name) {
            var el = document.querySelector('[name="' + name + '"]');
            if (!el) return;
            var box = el.closest('label') || el.parentElement;
            // sembunyikan juga teks bantuan yang tepat di bawahnya, bila ada di luar label
            var next = box.nextElementSibling;
            if (next && /^(SMALL|P)$/.test(next.tagName) && !next.querySelector('input, select, textarea')) {
                next.style.display = 'none';
            }
            box.style.display = 'none';
            el.disabled = true; // tidak ikut terkirim saat form disimpan
        });
    })();
</script>
@endif

</body>
</html>