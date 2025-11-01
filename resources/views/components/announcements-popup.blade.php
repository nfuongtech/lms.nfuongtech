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

<style>
  /* Tinh gọn giãn dòng trong popup: KHÔNG xoá code gốc, chỉ override */
  #announcements-popup .content { padding: 12px !important; }
  #announcements-popup .header h3,
  #announcements-popup .modal-title,
  #announcements-popup h1,
  #announcements-popup h2,
  #announcements-popup h3,
  #announcements-popup .title {
    margin-top: 3pt !important;
    margin-bottom: 3pt !important;
    line-height: 1.25 !important;
  }
  /* Khử margin lớn mặc định của Tailwind Typography (.prose) */
  #announcements-popup .prose :where(h1,h2,h3,h4){ 
    margin-top: 3pt !important; 
    margin-bottom: 3pt !important; 
    line-height: 1.3 !important;
  }
  #announcements-popup .prose :where(p,ul,ol,blockquote){
    margin-top: 6px !important;
    margin-bottom: 6px !important;
  }
  #announcements-popup .prose > :first-child{ margin-top: 0 !important; }
  #announcements-popup .prose > :last-child{ margin-bottom: 0 !important; }

  /* Đảm bảo ảnh không đẩy cao vùng trống; giữ tối đa 320px */
  #announcements-popup .panel img{
    width:100% !important; height:auto !important; display:block; object-fit:cover; max-height:320px !important;
  }

  /* Giữ panel vừa màn hình và cuộn được trên mobile */
  #announcements-popup .panel{
    max-height:92vh !important; overflow:auto !important; -webkit-overflow-scrolling:touch !important;
  }
</style>
<script>
  (function(){
    var root = document.getElementById('announcements-popup');
    if(!root) return;

    // Ẩn "Xem chi tiết" nếu có, giữ markup gốc
    root.querySelectorAll('a,button').forEach(function(el){
      var t = (el.textContent||'').trim().toLowerCase();
      if(t === 'xem chi tiết'){ el.style.display='none'; el.setAttribute('aria-hidden','true'); el.tabIndex=-1; }
    });

    // Link trong nội dung mở tab mới
    root.querySelectorAll('.prose a, .announcement-content a, .article-content a').forEach(function(a){
      a.setAttribute('target','_blank'); a.setAttribute('rel','noopener noreferrer');
    });

    // Loại bỏ <p> rỗng hoặc chỉ có <br> đầu/cuối để triệt tiêu khoảng trắng lớn
    var prose = root.querySelector('.prose');
    if (prose){
      var isEmptyP = function(p){
        var html = (p.innerHTML||'').trim();
        if (!html) return true;
        // Nếu chỉ có <br> hoặc các ký tự trắng
        return /^(\s|<br\s*\/?>)+$/i.test(html);
      };
      // Xóa p rỗng ở đầu
      while (prose.firstElementChild && prose.firstElementChild.tagName === 'P' && isEmptyP(prose.firstElementChild)){
        prose.removeChild(prose.firstElementChild);
      }
      // Xóa p rỗng ở cuối
      while (prose.lastElementChild && prose.lastElementChild.tagName === 'P' && isEmptyP(prose.lastElementChild)){
        prose.removeChild(prose.lastElementChild);
      }
    }
  })();
</script>

<!-- ====== OVERRIDE BỔ SUNG: Không in đậm “Đóng” + tăng khoảng cách chữ/viền (padding ngang) ====== -->
<style>
  /* Không in đậm; tăng padding trái-phải để khoảng cách chữ "Đóng" với viền rộng hơn */
  #announcements-popup .actions button{
    background:#FFFCD5 !important;
    color:#00529C !important;
    border-color:#00529C33 !important;
    font-weight:400 !important;         /* không in đậm */
    padding-left:16px !important;       /* tăng khoảng cách chữ↔viền trái */
    padding-right:16px !important;      /* tăng khoảng cách chữ↔viền phải */
    /* giữ nguyên padding dọc hiện tại; có thể chỉnh: padding-top/bottom nếu Sư phụ muốn */
  }

  /* Mobile tune nhẹ */
  @media (max-width: 480px){
    #announcements-popup .overlay{ padding:12px !important; }
    #announcements-popup .panel img{ max-height:32vh !important; }
  }
</style>

</div>
@endif
