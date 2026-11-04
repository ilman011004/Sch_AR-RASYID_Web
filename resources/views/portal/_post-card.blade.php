<article class="card post-card">
    @if ($post->image)
        <a href="{{ $post->publicUrl() }}">
            <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" loading="lazy">
        </a>
    @endif
    <div class="body">
        <div class="meta">
            <span class="tag {{ $post->type === 'pengumuman' ? 'tag-warn' : '' }}">{{ $post->typeLabel() }}</span>
            <span class="tag">{{ $post->school->isFoundation() ? 'Yayasan' : $post->school->name }}</span>
            <span class="date">{{ $post->published_at?->translatedFormat('d F Y') }}</span>
        </div>
        <h3><a href="{{ $post->publicUrl() }}">{{ $post->title }}</a></h3>
        <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 110) }}</p>
        <a class="more" href="{{ $post->publicUrl() }}">Baca selengkapnya →</a>
    </div>
</article>