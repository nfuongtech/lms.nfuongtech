@php
    use App\Models\Announcement;
    $popup = Announcement::active()->where('is_popup', true)->ordered()->first();
@endphp

@if($popup)
<div id="announcements-popup" x-data="annPopup()" x-init="init({{ $popup->id }})" style="position:relative">
    <style>
        /* === Popup: cố định kích thước hợp lý & chống tràn ngang === */
        #announcements-popup .overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:flex;align-items:center;justify-content:center;padding:16px}
        #announcements-popup .panel{
            width:calc(100% - 2rem);
            max-width:min(820px, 100%);
            background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e5e7eb
        }
        #announcements-popup .panel img{width:100% !important;max-width:100% !important;height:auto !important;display:block;object-fit:cover;max-height:320px}
        #announcements-popup .content{padding:16px}
        #announcements-popup .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:12px}
    </style>

    <div x-show="open" x-transition class="overlay" @keydown.escape.window="close()" @click.self="close()">
        <div class="panel" role="dialog" aria-modal="true">
            @if($popup->cover_path)
                <img src="{{ asset('storage/'.$popup->cover_path) }}" alt="">
            @endif
            <div class="content">
                <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a">{{ $popup->title }}</h3>
                <div class="prose prose-slate max-w-none" style="margin-top:8px;overflow-wrap:anywhere">{!! $popup->content !!}</div>
                <div class="actions">
                    <a href="{{ route('announcements.show',$popup->slug) }}" class="px-3 py-1.5" style="border-radius:10px;background:#0f172a;color:#fff;text-decoration:none">Xem chi tiết</a>
                    <button @click="close()" class="px-3 py-1.5" style="border-radius:10px;border:1px solid #e5e7eb;background:#fff">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function annPopup(){
        return {
            open:false, key:'lms_popup_seen',
            init(id){
                this.key = `lms_popup_seen_${id}_${(new Date()).toDateString()}`;
                if (!localStorage.getItem(this.key)) this.open = true;
            },
            close(){ this.open=false; localStorage.setItem(this.key,'1'); }
        };
    }
    </script>
</div>
@endif
