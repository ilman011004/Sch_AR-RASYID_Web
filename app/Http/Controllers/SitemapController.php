<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\School;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // 1. Domain Utama / Yayasan
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // Profil Yayasan
        foreach (['sejarah', 'visi-misi', 'struktur'] as $page) {
            $urls[] = [
                'loc' => route('portal.profile', $page),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // SPMB & Berita Yayasan
        $urls[] = [
            'loc' => route('portal.spmb'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('portal.announcements'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
        $urls[] = [
            'loc' => route('portal.news'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        // Berita Yayasan individual
        $posts = Post::published()->latest('published_at')->take(100)->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('portal.news.show', $post->slug),
                'lastmod' => $post->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 2. Jenjang Sekolah (PAUD, SD, SMP, SMA)
        $schools = School::jenjang()->where('is_active', true)->get();
        foreach ($schools as $school) {
            $urls[] = [
                'loc' => route('home', $school),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];

            // Profil jenjang
            foreach (['sejarah', 'visi-misi', 'struktur', 'pegawai'] as $page) {
                $urls[] = [
                    'loc' => route('profile', ['school' => $school, 'page' => $page]),
                    'lastmod' => now()->startOfMonth()->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }

            // Program jenjang
            foreach (['unggulan', 'kurikulum', 'ekstrakurikuler'] as $page) {
                $urls[] = [
                    'loc' => route('programs', ['school' => $school, 'page' => $page]),
                    'lastmod' => now()->startOfMonth()->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }

            // Berita, Galeri, Kontak, PPDB
            $urls[] = [
                'loc' => route('news', $school),
                'lastmod' => now()->startOfDay()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
            $urls[] = [
                'loc' => route('gallery', $school),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
            $urls[] = [
                'loc' => route('contact', $school),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
            $urls[] = [
                'loc' => route('ppdb', $school),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}

