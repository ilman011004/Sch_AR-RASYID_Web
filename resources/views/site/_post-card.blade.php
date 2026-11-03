<article class="card post-card">
    @if ($post->image)
        <a href="{{ route('news.show', ['school' => $school, 'slug' => $post->slug]) }}">
            <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" loading="lazy">
        </a>
    @endif
    <div class="body">
        <span class="tag {{ $post->type === 'pengumuman' ? 'tag-warn' : '' }}">{{ $post->typeLabel() }}</span>
        <h3><a href="{{ route('news.show', ['school' => $school, 'slug' => $post->slug]) }}">{{ $post->title }}</a></h3>
        <p class="muted small">{{ $post->published_at?->translatedFormat('d F Y') }}</p>
        <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 110) }}</p>
    </div>
</article>