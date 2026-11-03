<article class="card post-card">
    @if ($post->image)
        <a href="{{ $post->publicUrl() }}">
            <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" loading="lazy">
        </a>
    @endif
    <div class="body">
        <span class="tag {{ $post->type === 'pengumuman' ? 'tag-warn' : '' }}">{{ $post->typeLabel() }}</span>
        <span class="tag">{{ $post->school->isFoundation() ? 'Yayasan' : $post->school->name }}</span>
        <h3><a href="{{ $post->publicUrl() }}">{{ $post->title }}</a></h3>
        <p class="muted small">{{ $post->published_at?->translatedFormat('d F Y') }}</p>
        <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 110) }}</p>
    </div>
</article>