<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\School;
use Illuminate\Http\Request;

/**
 * Situs induk yayasan. $foundation dan $jenjang dibagikan ke semua view
 * portal.* lewat View::composer di AppServiceProvider.
 */
class PortalController extends Controller
{
    private const PROFILE_PAGES = [
        'sejarah' => 'Sejarah',
        'visi-misi' => 'Visi, Misi, dan Tujuan',
        'struktur' => 'Struktur Organisasi',
        'pegawai' => 'Pegawai',
    ];

    public function home()
    {
        return view('portal.home', [
            'slides' => School::foundation()->slides()->orderBy('sort')->orderBy('id')->get(),
            'news' => Post::forPortal()->latest('published_at')->take(3)->get(),
            'announcements' => Post::forPortal()->where('type', 'pengumuman')->latest('published_at')->take(3)->get(),
        ]);
    }

    public function profile(string $page)
    {
        abort_unless(isset(self::PROFILE_PAGES[$page]), 404);

        $staff = collect();

        if ($page === 'pegawai') {
            $staff = School::foundation()->staff()->orderBy('sort')->orderBy('id')->get();
        }

        return view('portal.profile', [
            'page' => $page,
            'title' => self::PROFILE_PAGES[$page],
            'staff' => $staff,
        ]);
    }

    public function spmb()
    {
        return view('portal.spmb', ['sections' => School::foundation()->ppdbSections()]);
    }

    public function announcements()
    {
        $posts = Post::forPortal()->where('type', 'pengumuman')
            ->latest('published_at')->paginate(9);

        return view('portal.announcements', ['posts' => $posts]);
    }

    public function news(Request $request)
    {
        $source = $request->string('sumber')->toString();

        $posts = Post::forPortal()
            ->when($source !== '', fn ($q) => $q->whereHas('school', fn ($s) => $s->where('slug', $source)))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('portal.news', ['posts' => $posts, 'source' => $source]);
    }

    /** Detail tulisan milik yayasan. Tulisan jenjang dibuka di situs jenjangnya. */
    public function newsShow(string $slug)
    {
        $post = School::foundation()->posts()->published()->where('slug', $slug)->firstOrFail();

        $otherNews = Post::forPortal()->where('id', '!=', $post->id)
            ->where('type', 'berita')
            ->latest('published_at')->take(5)->get();

        $announcements = Post::forPortal()->where('id', '!=', $post->id)
            ->where('type', 'pengumuman')
            ->latest('published_at')->take(5)->get();

        $others = Post::forPortal()->where('id', '!=', $post->id)
            ->latest('published_at')->take(5)->get();

        return view('portal.news-show', [
            'post' => $post,
            'otherNews' => $otherNews,
            'announcements' => $announcements,
            'others' => $others,
        ]);
    }
}