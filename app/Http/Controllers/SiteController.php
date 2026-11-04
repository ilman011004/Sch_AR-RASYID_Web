<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\School;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /** Halaman di bawah menu Profil. */
    private const PROFILE_PAGES = [
        'sejarah' => 'Sejarah',
        'visi-misi' => 'Visi, Misi, dan Tujuan',
        'struktur' => 'Struktur Organisasi',
        'pegawai' => 'Pegawai',
    ];

    /** Halaman di bawah menu Program Akademik. */
    private const PROGRAM_PAGES = [
        'unggulan' => 'Program Unggulan',
        'kurikulum' => 'Kurikulum',
        'ekstrakurikuler' => 'Kegiatan Ekstrakurikuler',
    ];

    public function home(School $school)
    {
        return view('site.home', [
            'school' => $school,
            'slides' => $school->slides()->orderBy('sort')->orderBy('id')->get(),
            'advantages' => $school->advantages()->orderBy('sort')->orderBy('id')->get(),
            'news' => $school->posts()->published()->latest('published_at')->take(3)->get(),
            'photos' => $school->galleries()->latest()->take(6)->get(),
            'faqs' => $school->faqs()->orderBy('sort')->orderBy('id')->get(),
            'stats' => [
                'Siswa aktif' => $school->stat_students ?? 0,
                'Tenaga pendidik & kependidikan' => $school->stat_staff ?? $school->staff()->count(),
                'Prestasi' => $school->stat_achievements ?? $school->galleries()->where('category', 'prestasi')->count(),
            ],
        ]);
    }

    public function profileIndex(School $school)
    {
        return redirect()->route('profile', ['school' => $school, 'page' => 'sejarah']);
    }

    public function profile(School $school, string $page)
    {
        abort_unless(isset(self::PROFILE_PAGES[$page]), 404);

        $staff = collect();

        if ($page === 'pegawai') {
            $staff = $school->staff()->orderBy('sort')->orderBy('id')->get()->groupBy('staff_group');
        }

        return view('site.profile', [
            'school' => $school,
            'page' => $page,
            'title' => self::PROFILE_PAGES[$page],
            'educators' => $staff->get('pendidik', collect()),
            'support' => $staff->get('kependidikan', collect()),
        ]);
    }

    public function programsIndex(School $school)
    {
        return redirect()->route('programs', ['school' => $school, 'page' => 'unggulan']);
    }

    public function programs(School $school, string $page)
    {
        abort_unless(isset(self::PROGRAM_PAGES[$page]), 404);

        $items = collect();

        if (in_array($page, ['unggulan', 'ekstrakurikuler'], true)) {
            $items = $school->programs()->where('kind', $page)->orderBy('sort')->orderBy('id')->get();
        }

        return view('site.programs', [
            'school' => $school,
            'page' => $page,
            'title' => self::PROGRAM_PAGES[$page],
            'items' => $items,
        ]);
    }

    public function news(Request $request, School $school)
    {
        $type = $request->query('jenis');

        $posts = $school->posts()->published()
            ->when(in_array($type, ['berita', 'pengumuman'], true), fn ($q) => $q->where('type', $type))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('site.news', ['school' => $school, 'posts' => $posts, 'type' => $type]);
    }

    public function newsShow(School $school, string $slug)
    {
        $post = $school->posts()->published()->where('slug', $slug)->firstOrFail();

        $others = $school->posts()->published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')->take(3)->get();

        return view('site.news-show', ['school' => $school, 'post' => $post, 'others' => $others]);
    }

    public function gallery(Request $request, School $school)
    {
        $category = $request->query('kategori');

        $photos = $school->galleries()
            ->when(in_array($category, ['pembelajaran', 'acara', 'prestasi'], true), fn ($q) => $q->where('category', $category))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('site.gallery', ['school' => $school, 'photos' => $photos, 'category' => $category]);
    }

    public function contact(School $school)
    {
        return view('site.contact', ['school' => $school]);
    }

    public function contactStore(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]*$/'],
            'message' => 'required|string|max:2000',
            'website' => 'prohibited', // kolom jebakan untuk bot; manusia tidak melihatnya
        ]);

        unset($data['website']);

        ContactMessage::create($data + ['school_id' => $school->id]);

        return redirect()->route('contact', $school)
            ->with('status', 'Terima kasih, pesan Anda sudah kami terima.');
    }
}