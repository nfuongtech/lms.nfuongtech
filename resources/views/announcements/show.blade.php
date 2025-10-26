<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>{{ $post->title }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--text:#0f172a;--muted:#64748b;--border:#e5e7eb;--bg:#ffffff;}
    body{margin:0;background:#f8fafc;color:var(--text);}
    .container{max-width:1000px;margin:0 auto;padding:16px;}
    .breadcrumb{font-size:12px;color:var(--muted);margin-bottom:8px}
    .h1{font-weight:700;font-size:26px}
    .meta{color:var(--muted);font-size:12px;margin-top:6px}
    .cover{width:100%;height:auto;display:block;border-radius:12px;margin-top:16px}
    .aspect{position:relative;width:100%;padding-top:56.25%;margin-top:16px}
    .aspect iframe,.aspect video{position:absolute;inset:0;width:100%;height:100%;border:0;border-radius:12px}
    .prose{line-height:1.75;margin-top:16px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:24px}
    .card{display:block;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px;text-decoration:none;color:inherit}
    .card img{width:100%;height:140px;object-fit:cover;border-radius:8px;margin-bottom:8px}
    @media (min-width:768px){.h1{font-size:30px}}
  </style>
</head>
<body>
  <div class="container">
    <div class="breadcrumb"><a href="{{ url('/') }}">Trang chủ</a> / Thông báo & Tuyển sinh</div>
    <h1 class="h1">{{ $post->title }}</h1>
    <div class="meta">
      @if($post->publish_at) {{ $post->publish_at->format('H:i d/m/Y') }} @endif
      @if($post->is_featured) • Tiêu điểm @endif
    </div>

    @if($post->cover_path)
      <img class="cover" src="{{ asset('storage/'.$post->cover_path) }}" alt="">
    @endif

    @if($post->video_url)
      <div class="aspect"><iframe src="{{ $post->video_url }}" allowfullscreen></iframe></div>
    @elseif($post->video_path)
      <div class="aspect"><video controls><source src="{{ asset('storage/'.$post->video_path) }}"></video></div>
    @endif

    <article class="prose">{!! $post->content !!}</article>

    @if($others->count())
      <h2 style="margin-top:24px;font-weight:600">Tin khác</h2>
      <div class="grid">
        @foreach($others as $item)
          <a class="card" href="{{ route('announcements.show', $item->slug) }}">
            @if($item->cover_path)
              <img src="{{ asset('storage/'.$item->cover_path) }}" alt="">
            @endif
            <div style="font-size:12px;color:var(--muted)">{{ optional($item->publish_at)->format('d/m/Y') }}</div>
            <div style="font-weight:600">{{ $item->title }}</div>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</body>
</html>
