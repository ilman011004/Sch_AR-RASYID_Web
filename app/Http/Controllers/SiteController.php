<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        $milestones = collect();

        if ($page === 'pegawai') {
            $staff = $school->staff()->orderBy('sort')->orderBy('id')->get()->groupBy('staff_group');
        }

        if ($page === 'sejarah') {
            $milestones = $school->milestones()->orderBy('sort')->orderBy('year')->orderBy('id')->get();
        }

        return view('site.profile', [
            'school' => $school,
            'page' => $page,
            'title' => self::PROFILE_PAGES[$page],
            'milestones' => $milestones,
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

        // Kotak samping: berita lain, lalu pengumuman lain (tanpa tulisan yang sedang dibuka)
        $otherNews = $school->posts()->published()
            ->where('id', '!=', $post->id)
            ->where('type', 'berita')
            ->latest('published_at')->take(5)->get();

        $announcements = $school->posts()->published()
            ->where('id', '!=', $post->id)
            ->where('type', 'pengumuman')
            ->latest('published_at')->take(5)->get();

        return view('site.news-show', [
            'school' => $school,
            'post' => $post,
            'otherNews' => $otherNews,
            'announcements' => $announcements,
        ]);
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
        // Tamu dikembalikan ke halaman ini setelah selesai login
        if (! auth()->check()) {
            session(['url.intended' => route('contact', $school)]);
        }

        return view('site.contact', ['school' => $school]);
    }

    public function contactStore(Request $request, School $school)
    {
        $user = $request->user();

        // Wajib login
        if (! $user) {
            session(['url.intended' => route('contact', $school)]);

            return redirect()->route('ppdb.login', $school)
                ->with('status', 'Silakan masuk terlebih dahulu untuk mengirim pesan.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        $contact = ContactMessage::create([
            'school_id' => $school->id, // dari alamat halaman, bukan dari isian formulir
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'message' => $data['message'],
        ]);

        // Email hanya ke sekolah jenjang ini. Bila gagal kirim, pesan tetap tersimpan.
        if (filled($school->email)) {
            try {
                Mail::to($school->email)->send(new ContactMessageReceived($contact, $school));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('contact', $school)
            ->with('status', 'Terima kasih, pesan Anda sudah kami terima.');
    }
}