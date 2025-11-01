@php
    use App\Models\Announcement;

    // Lấy tin hợp lệ theo thứ tự yêu cầu
    $items = Announcement::active()->ordered()->take(10)->get();

    // Nếu có Tiêu điểm thì ưu tiên hiển thị nhóm đó
    $featured = $items->where('is_featured', true);
    if ($featured->count() > 0) {
        $items = $featured->values();
    } else {
        $items = $items->values();
    }

    // Cài đặt từ bản ghi đầu tiên
    $speedSec  = (int)($items->first()->scroll_speed ?? 6);
    $speedSec  = $speedSec >= 2 ? $speedSec : 6;
    $autoSlide = (bool)($items->first()->enable_marquee ?? true);

    // Hàm kiểm tra URL/video extension để quyết định <video> (tự cao) hay <iframe> (nhúng)
    function is_direct_video_url(?string $url): bool {
        if (!$url) return false;
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        return in_array($ext, ['mp4','webm','ogg','ogv','mov','m4v','mkv','avi','mpeg','mpg']);
    }
@endphp

<section id="announcements-widget" class="mt-8" style="overflow:hidden">
    <style>
        /* ===== Widget 1 khối: ép theo chiều ngang, chiều cao tự theo ảnh/video ===== */
        #announcements-widget{max-width:100%}
        #announcements-widget *{box-sizing:border-box}

        /* Tiêu đề căn giữa - Đồng nhất với các section khác */
        #announcements-widget .heading{
            font-size:18px;
            font-weight:700;
            text-align:center;
            margin-bottom:12px;
        }

        /* Khung hero: không dùng wrapper 16:9 để tránh khoảng trống; media tự cao */
        #announcements-widget .hero{
            position:relative;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 12px 32px rgba(15, 23, 42, 0.1);
        }

        /* Media: ép full ngang, cao tự nhiên */
        #announcements-widget .media{
            width:100% !important;
            max-width:100% !important;
            position:relative;
        }
        #announcements-widget .media img{
            display:block;
            width:100% !important;
            height:auto !important;
            max-width:100% !important;
            object-fit:contain;
            border:0;
        }
        #announcements-widget .media video{
            display:block;
            width:100% !important;
            height:auto !important;
            max-width:100% !important;
            object-fit:contain;
            border:0;
            background:#000;
        }
        /* Với iframe (YouTube/Vimeo) không thể auto-height theo nội dung do cross-origin.
           Dùng width:100% và chiều cao responsive hợp lý theo viewport để tránh tràn/ngắn 150px mặc định. */
        #announcements-widget .media iframe{
            display:block;
            width:100% !important;
            border:0;
            height:clamp(240px, 56.25vw, 720px); /* cao linh hoạt theo viewport, vẫn full ngang */
            max-width:100% !important;
            background:#000;
        }

        /* Fallback cho nội dung văn bản khi không có media */
        #announcements-widget .content-fallback{
            padding:20px;
            background:#f9fafb;
            border-radius:12px;
            line-height:1.6;
            color:#374151;
            max-height:400px;
            overflow-y:auto;
        }
        #announcements-widget .content-fallback a{
            color:#2563eb;
            text-decoration:underline;
        }

        /* Overlay tiêu đề đặt trên cùng, không chiếm chỗ */
        #announcements-widget .overlay{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            background:linear-gradient(to top,rgba(0,0,0,.65),transparent);
            padding:12px;
            pointer-events:none;
        }
        #announcements-widget .overlay .date{
            color:#fff;
            opacity:.95;
            font-size:.875rem;
            margin-bottom:2px;
        }
        #announcements-widget .overlay .title{
            color:#fff;
            font-weight:700;
            display:-webkit-box;
            -webkit-line-clamp:2;
            -webkit-box-orient:vertical;
            overflow:hidden;
        }

        /* Chấm điều hướng */
        #announcements-widget .dots{
            display:flex;
            justify-content:center;
            gap:8px;
            padding:8px;
        }
        #announcements-widget .dot{
            width:10px;
            height:10px;
            border-radius:999px;
            background:#cbd5e1;
            border:0;
            cursor:pointer;
            transition:all .2s ease;
        }
        #announcements-widget .dot:hover{
            background:#94a3b8;
        }
        #announcements-widget .dot.is-active{
            background:#0f172a;
        }

        /* Hiệu ứng chuyển tiếp Wipe from Left */
        #announcements-widget .slide-item{
            display:block;
            position:relative;
        }
        #announcements-widget .slide-enter{
            animation: wipeFromLeft 0.5s ease-out;
        }
        @keyframes wipeFromLeft {
            from {
                clip-path: inset(0 100% 0 0);
                transform: translateX(-20px);
            }
            to {
                clip-path: inset(0 0 0 0);
                transform: translateX(0);
            }
        }
    </style>

    <div class="heading">THÔNG BÁO & TUYỂN SINH</div>

    <div class="hero" x-data="heroSlider({interval: {{ $speedSec * 1000 }}, auto: {{ $autoSlide ? 'true':'false' }}})" x-init="init()">
        <div class="media" style="position:relative">
            <template x-for="(item, idx) in items" :key="idx">
                <a :href="item.url" x-show="active===idx" class="slide-item" :class="active===idx ? 'slide-enter' : ''" target="_blank" rel="noopener noreferrer">
                    <!-- Ưu tiên: video self-host (video_path) hoặc direct video URL => <video> (chiều cao tự nhiên) -->
                    <template x-if="item.videoType === 'html5'">
                        <video :src="item.video" controls playsinline></video>
                    </template>

                    <!-- Nếu không phải direct video URL: dùng iframe (YouTube/Vimeo). Cao responsive theo viewport -->
                    <template x-if="item.videoType === 'iframe'">
                        <iframe :src="item.video" allowfullscreen title="video-embed"></iframe>
                    </template>

                    <!-- Nếu không có video nhưng có ảnh bìa: hiển thị ảnh -->
                    <template x-if="!item.videoType && item.cover && !item.cover.includes('placehold.co')">
                        <img :src="item.cover" alt="ann-cover">
                    </template>

                    <!-- Nếu không có video và không có ảnh: hiển thị nội dung văn bản -->
                    <template x-if="!item.videoType && (!item.cover || item.cover.includes('placehold.co'))">
                        <div class="content-fallback" x-html="item.contentPreview"></div>
                    </template>

                    <div class="overlay">
                        <div class="date" x-text="item.date"></div>
                        <div class="title" x-text="item.title"></div>
                    </div>
                </a>
            </template>
        </div>

        <div class="dots" x-show="items.length > 1">
            <template x-for="(item, idx) in items" :key="'dot'+idx">
                <button @click="go(idx)" class="dot" :class="active===idx ? 'is-active' : ''" aria-label="slide"></button>
            </template>
        </div>
    </div>

    <script>
    function heroSlider({interval=6000, auto=true}) {
        return {
            items: [
                @foreach($items as $it)
                {
                    title: @js($it->title),
                    date:  @js(optional($it->publish_at)->format('d/m/Y')),
                    url:   @js(route('announcements.show',$it->slug)),
                    cover: @js($it->cover_path ? asset('storage/'.$it->cover_path) : null),
                    contentPreview: @js($it->content ? strip_tags(Str::limit($it->content, 300)) : ''),
                    @php
                        $videoType = null; $videoSrc = null;
                        if ($it->video_path) {
                            $videoType = 'html5'; $videoSrc = asset('storage/'.$it->video_path);
                        } elseif (is_direct_video_url($it->video_url ?? null)) {
                            $videoType = 'html5'; $videoSrc = $it->video_url;
                        } elseif (!empty($it->video_url)) {
                            $videoType = 'iframe'; $videoSrc = $it->video_url;
                        }
                    @endphp
                    videoType: @js($videoType),
                    video: @js($videoSrc),
                },
                @endforeach
            ],
            active: 0, t: null,
            init() {
                if (!auto || this.items.length <= 1) return;
                this.t = setInterval(() => this.next(), interval);
                window.addEventListener('visibilitychange', () => {
                    if (document.hidden) { if (this.t) clearInterval(this.t); }
                    else { if (auto) this.t = setInterval(() => this.next(), interval); }
                });
                
                // Make all content links open in new tab
                this.$nextTick(() => {
                    this.$el.querySelectorAll('.content-fallback a').forEach(link => {
                        link.setAttribute('target', '_blank');
                        link.setAttribute('rel', 'noopener noreferrer');
                    });
                });
            },
            next(){ this.active = (this.active+1) % this.items.length; },
            go(i){ this.active=i; }
        };
    }
    </script>
</section>
