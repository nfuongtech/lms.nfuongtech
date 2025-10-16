<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Kế hoạch đào tạo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root {
      --border:#e5e7eb;
      --surface:#f9fafb;
      --surface-strong:#ffffff;
      --text:#111827;
      --muted:#6b7280;
      --brand:#111827;
      --accent:#2563eb;
      --accent-soft:#dbeafe;
      --today-bg:#dcfce7;
      --badge-gray:#e5e7eb;
      --badge-info:#dbeafe;
      --badge-warn:#fef3c7;
      --badge-ok:#dcfce7;
      --badge-pause:#fee2e2;
      --shadow:0 12px 32px rgba(15, 23, 42, 0.1);
    }

    *, *::before, *::after { box-sizing:border-box; }

    [hidden] { display:none !important; }

    html, body { height:100%; }

    body {
      font-family:system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      margin:0;
      padding:24px;
      display:flex;
      flex-direction:column;
      min-height:100vh;
      color:var(--text);
      background:linear-gradient(180deg, #f5f7fb 0%, #fff 100%);
    }

    .topbar {
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
      flex-wrap:wrap;
    }

    .brand {
      font-weight:800;
      letter-spacing:.3px;
      font-size:150%;
      white-space:nowrap;
    }

    .brand a {
      text-decoration:none;
      color:var(--brand);
    }

    .login-wrap {
      display:flex;
      flex-direction:column;
      gap:8px;
      align-items:stretch;
      width:100%;
      max-width:680px;
    }

    .login-form {
      display:grid;
      grid-template-columns:1fr;
      grid-auto-rows:auto;
      gap:8px;
      width:100%;
      background:var(--surface-strong);
      padding:12px;
      border-radius:12px;
      box-shadow:0 6px 18px rgba(15, 23, 42, 0.05);
    }

    .login-row { min-width:0; }

    .login-row input {
      width:100%;
      box-sizing:border-box;
      padding:10px 12px;
      border:1px solid var(--border);
      border-radius:8px;
    }

    .login-actions {
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      align-items:center;
      justify-content:flex-end;
      width:100%;
    }

    .remember {
      display:flex;
      align-items:center;
      gap:6px;
      font-size:14px;
      color:#374151;
    }

    .btn {
      padding:9px 14px;
      border-radius:8px;
      border:1px solid var(--border);
      background:#fff;
      color:var(--text);
      cursor:pointer;
      font-weight:600;
      transition:all .2s ease;
    }

    .btn:hover { box-shadow:0 4px 12px rgba(37, 99, 235, 0.12); }

    .btn-primary {
      background:var(--accent);
      color:#fff;
      border-color:var(--accent);
    }

    .content {
      flex:1;
      display:flex;
      flex-direction:column;
      gap:40px;
      margin-top:28px;
    }

    .section-title {
      font-size:22px;
      margin-bottom:12px;
      font-weight:700;
    }

    .filters {
      margin:12px 0 18px;
      display:flex;
      gap:12px;
      align-items:center;
      flex-wrap:wrap;
    }

    .filters label {
      display:flex;
      align-items:center;
      gap:6px;
      font-size:14px;
    }

    .filters label select {
      padding:8px 10px;
      border-radius:8px;
      border:1px solid var(--border);
      background:#fff;
    }

    .table-wrap {
      width:100%;
      overflow-x:auto;
      background:var(--surface-strong);
      border-radius:16px;
      box-shadow:var(--shadow);
    }

    table {
      width:100%;
      border-collapse:collapse;
      min-width:960px;
    }

    th {
      background:#f3f4f6;
      text-align:center;
      border-bottom:1px solid var(--border);
      padding:12px 14px;
      font-size:14px;
      font-weight:600;
      color:#1f2937;
    }

    td {
      border-top:1px solid var(--border);
      padding:12px 14px;
      vertical-align:middle;
      text-align:center;
      font-size:14px;
    }

    tbody tr:first-child td { border-top:none; }

    td.left, th.left { text-align:left; }

    .badge {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:4px 10px;
      border-radius:999px;
      font-size:12px;
      color:var(--text);
      font-weight:600;
    }

    .badge.gray { background:var(--badge-gray); }
    .badge.info { background:var(--badge-info); }
    .badge.warn { background:var(--badge-warn); }
    .badge.ok { background:var(--badge-ok); }
    .badge.pause { background:var(--badge-pause); }

    .status-note {
      margin-top:6px;
      font-size:13px;
      color:#b91c1c;
      text-align:left;
      line-height:1.4;
      white-space:pre-wrap;
    }

    .nowrap { white-space:nowrap; }

    .session.today {
      background:var(--today-bg);
      padding:2px 8px;
      border-radius:6px;
      display:inline-block;
    }

    .link-btn {
      background:none;
      border:none;
      color:var(--accent);
      cursor:pointer;
      font-weight:600;
      text-decoration:underline;
      padding:0;
    }

    .link-btn:hover { opacity:.8; }

    .lookup {
      background:var(--surface-strong);
      border-radius:20px;
      padding:24px;
      box-shadow:var(--shadow);
    }

    .lookup-form {
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      align-items:center;
      margin-bottom:16px;
    }

    .lookup-input {
      flex:1 1 260px;
      padding:10px 14px;
      border:1px solid var(--border);
      border-radius:10px;
      font-size:15px;
    }

    .lookup-message {
      font-size:14px;
      color:var(--muted);
      margin-bottom:16px;
    }

    .lookup-results {
      display:flex;
      flex-direction:column;
      gap:20px;
    }

    .lookup-panel h3 {
      margin:0 0 12px;
      font-size:18px;
    }

    .lookup-panel .table-wrap {
      box-shadow:none;
      border-radius:12px;
    }

    .lookup-table {
      min-width:880px;
    }

    .empty-row {
      text-align:center;
      color:var(--muted);
      padding:16px;
    }

    .featured {
      display:flex;
      flex-direction:column;
      gap:20px;
    }

    .featured-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
      gap:20px;
    }

    .featured-column {
      background:var(--surface-strong);
      border-radius:20px;
      padding:24px;
      box-shadow:var(--shadow);
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .featured-column h3 {
      margin:0;
      font-size:18px;
    }

    .card-stack {
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .student-card {
      display:flex;
      gap:14px;
      align-items:center;
      padding:16px;
      border-radius:14px;
      border:1px solid rgba(37, 99, 235, 0.08);
      background:linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(255,255,255,0.95));
    }

    .student-avatar {
      width:64px;
      height:64px;
      border-radius:50%;
      overflow:hidden;
      flex-shrink:0;
      background:var(--accent-soft);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      color:var(--accent);
      font-size:20px;
    }

    .student-avatar img {
      width:100%;
      height:100%;
      object-fit:cover;
    }

    .student-info {
      display:flex;
      flex-direction:column;
      gap:4px;
      font-size:14px;
    }

    .student-name {
      font-weight:700;
      font-size:15px;
    }

    .student-meta {
      color:var(--muted);
    }

    .student-achievement {
      margin-top:4px;
      font-weight:600;
      color:var(--accent);
    }

    .empty-text {
      color:var(--muted);
      font-size:14px;
    }

    footer {
      margin-top:40px;
      padding-top:16px;
      color:var(--muted);
      font-size:14px;
      text-align:center;
    }

    .sr-only {
      position:absolute;
      width:1px;
      height:1px;
      padding:0;
      margin:-1px;
      overflow:hidden;
      clip:rect(0,0,0,0);
      white-space:nowrap;
      border:0;
    }

    .modal-open { overflow:hidden; }

    .modal {
      position:fixed;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      z-index:50;
    }

    .modal-backdrop {
      position:absolute;
      inset:0;
      background:rgba(15,23,42,0.55);
    }

    .modal-card {
      position:relative;
      z-index:1;
      width:min(920px, 92vw);
      max-height:85vh;
      background:var(--surface-strong);
      border-radius:20px;
      box-shadow:var(--shadow);
      padding:24px;
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .modal-header {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
    }

    .modal-title {
      font-size:20px;
      margin:0;
    }

    .modal-actions {
      display:flex;
      align-items:center;
      gap:10px;
    }

    .modal-print-brand {
      display:none;
      font-weight:700;
      font-size:14px;
      color:#0f172a;
      text-transform:uppercase;
    }

    .btn-print { white-space:nowrap; }

    .modal-close {
      background:none;
      border:none;
      font-size:24px;
      line-height:1;
      cursor:pointer;
      color:var(--muted);
    }

    .modal-table {
      min-width:720px;
    }

    .modal-loader,
    .modal-empty {
      text-align:center;
      color:var(--muted);
      font-size:14px;
      padding:12px 0;
    }

    @media (min-width:768px) {
      .login-wrap { align-items:flex-end; }
      .login-form {
        grid-template-columns:1fr 1fr;
        grid-template-areas:
          "user pass"
          "actions actions";
        column-gap:12px;
        row-gap:10px;
      }
      .login-user { grid-area:user; }
      .login-pass { grid-area:pass; }
      .login-actions { grid-area:actions; justify-content:flex-end; }
      .lookup-results { flex-direction:column; }
    }

    @media (max-width:540px) {
      body { padding:18px; }
      .brand { font-size:135%; }
      .table-wrap { border-radius:12px; }
      .lookup { padding:18px; }
      .featured-column { padding:18px; }
      .modal-card { padding:18px; }
    }

    @media print {
      body {
        background:#fff;
        padding:0;
        color:#000;
      }

      .topbar,
      .content,
      footer,
      .modal-backdrop,
      .modal-close,
      .modal-actions { display:none !important; }

      .modal {
        position:static;
        inset:auto;
        display:block;
        padding:0;
      }

      .modal-card {
        width:100%;
        max-height:none;
        box-shadow:none;
        border-radius:0;
        padding:0;
      }

      .modal-card .table-wrap {
        box-shadow:none;
        border-radius:0;
      }

      .modal-header {
        display:block;
        text-align:center;
        padding:40px 24px 16px;
        position:relative;
      }

      .modal-print-brand {
        display:block !important;
        position:absolute;
        top:0;
        left:24px;
      }

      .modal-title {
        font-size:20px;
        margin:0;
        text-align:center;
      }

      .modal-table {
        width:100%;
        min-width:0;
      }
    }
  </style>
</head>
<body data-registrations-url="{{ route('home.registrations', ['khoaHoc' => '__ID__']) }}" data-lookup-url="{{ route('home.lookup') }}">
  <div class="topbar">
    <div class="brand"><a href="/">QUẢN LÝ ĐÀO TẠO &amp; HỌC VIÊN</a></div>

    <div class="login-wrap">
      @auth
        <div style="font-size:14px; color:#374151;">
          Xin chào,
          <strong>{{ auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email }}</strong>
        </div>
        <div style="display:flex; gap:8px">
          <a class="btn" href="/admin">Vào Admin</a>
          <form method="post" action="{{ route('site.logout') }}">
            @csrf
            <button type="submit" class="btn">Đăng xuất</button>
          </form>
        </div>
      @else
        @if ($errors->has('auth'))
          <div style="color:#b91c1c; font-size:13px; text-align:right">{{ $errors->first('auth') }}</div>
        @endif
        <form class="login-form" method="post" action="{{ route('site.login') }}">
          @csrf
          <div class="login-row login-user">
            <input type="text" name="user" value="{{ old('user') }}" placeholder="User / Email" required>
          </div>
          <div class="login-row login-pass">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <div class="login-actions">
            <label class="remember">
              <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
              Ghi nhớ
            </label>
            <button type="submit" class="btn btn-primary">Đăng nhập</button>
          </div>
        </form>
      @endauth
    </div>
  </div>

  <main class="content">
    <section>
      <h2 class="section-title">Kế hoạch đào tạo</h2>

      <form class="filters" method="get" action="" id="filterForm">
        <label>Tuần:
          <select id="week" name="week" onchange="onWeekChange()">
            <option value="">— Không lọc theo tuần —</option>
            @foreach($weeks as $w)
              <option value="{{ $w }}" {{ $filterMode==='week' && (int)$week===(int)$w ? 'selected' : '' }}>{{ $w }}</option>
            @endforeach
          </select>
        </label>

        <label>Tháng:
          <select id="month" name="month" onchange="onMonthChange()">
            <option value="">— Không lọc theo tháng —</option>
            @foreach($months as $m)
              <option value="{{ $m }}" {{ $filterMode==='month' && (int)$month===(int)$m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </label>

        <label>Năm:
          <select id="year" name="year" onchange="onYearChange()">
            <option value="">— Không lọc theo năm —</option>
            @foreach($years as $y)
              <option value="{{ $y }}" {{ (int)$year===(int)$y && $filterMode!=='week' ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </label>

        <noscript><button type="submit" class="btn">Lọc</button></noscript>
      </form>

      <div class="table-wrap">
        <table class="training-table">
          <thead>
            <tr>
              <th class="nowrap">TT</th>
              <th class="nowrap">Mã khóa</th>
              <th>Tên khóa học</th>
              <th>Giảng viên</th>
              <th>Ngày, Giờ đào tạo</th>
              <th>Địa điểm</th>
              <th class="nowrap">Tuần</th>
              <th class="nowrap">DS Học viên</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td class="nowrap">{{ $i + 1 }}</td>
                <td class="nowrap">{{ $r['ma_khoa_hoc'] }}</td>
                <td class="left">{{ $r['ten_khoa_hoc'] }}</td>
                <td>{{ $r['giang_vien'] }}</td>
                <td>{!! $r['ngay_gio_html'] !!}</td>
                <td>{!! $r['dia_diem_html'] !!}</td>
                <td class="nowrap">{{ $r['tuan'] }}</td>
                <td class="nowrap">
                  @if($r['registered_students_count'] > 0)
                    <button
                      type="button"
                      class="link-btn"
                      data-course="{{ $r['id'] }}"
                      data-course-name="{{ $r['ten_khoa_hoc'] }}"
                      data-course-schedule="{{ $r['primary_schedule_text'] }}"
                      data-course-location="{{ $r['primary_location_text'] }}"
                    >
                      {{ $r['registered_students_count'] }} học viên
                    </button>
                  @else
                    <span style="color:var(--muted);">Chưa có</span>
                  @endif
                </td>
                <td>
                  @php $classes = ['Dự thảo'=>'gray','Ban hành'=>'info','Đang đào tạo'=>'warn','Kết thúc'=>'ok','Tạm hoãn'=>'pause']; @endphp
                  <span class="badge {{ $classes[$r['trang_thai']] ?? 'gray' }}">{{ $r['trang_thai'] }}</span>
                  @if($r['trang_thai'] === 'Tạm hoãn' && $r['ly_do_tam_hoan'] !== '')
                    <div class="status-note">{!! nl2br(e($r['ly_do_tam_hoan'])) !!}</div>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="empty-row">Không có dữ liệu.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="lookup" id="lookupSection">
      <h2 class="section-title">Tra cứu kết quả học tập</h2>
      <form class="lookup-form" id="lookupForm">
        <label class="sr-only" for="lookupInput">Nhập Mã số, Họ &amp; Tên hoặc Email</label>
        <input type="text" class="lookup-input" id="lookupInput" name="q" placeholder="Nhập Mã số, Họ &amp; Tên hoặc Email để tra cứu" autocomplete="off">
        <button type="submit" class="btn btn-primary">Tra cứu</button>
      </form>
      <div class="lookup-message" id="lookupMessage" hidden></div>
      <div class="lookup-results" id="lookupResults" hidden>
        <div class="lookup-panel">
          <h3>Khóa học đã hoàn thành</h3>
          <div class="table-wrap">
            <table class="lookup-table" id="completedTable">
              <thead>
                <tr>
                  <th>TT</th>
                  <th>MS</th>
                  <th>Họ &amp; Tên</th>
                  <th>Công ty/Ban NVQT</th>
                  <th>THACO/TĐTV</th>
                  <th>Tên khóa học</th>
                  <th>Mã khóa</th>
                  <th>ĐTB</th>
                  <th>Giờ thực học</th>
                  <th>Ngày hoàn thành</th>
                  <th>Chi phí đào tạo</th>
                  <th>Chứng nhận</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
            <div class="modal-empty" id="completedEmpty" hidden>Chưa có dữ liệu phù hợp.</div>
          </div>
        </div>
        <div class="lookup-panel">
          <h3>Khóa học chưa hoàn thành</h3>
          <div class="table-wrap">
            <table class="lookup-table" id="incompletedTable">
              <thead>
                <tr>
                  <th>TT</th>
                  <th>MS</th>
                  <th>Họ &amp; Tên</th>
                  <th>Công ty/Ban NVQT</th>
                  <th>THACO/TĐTV</th>
                  <th>Tên khóa học</th>
                  <th>Mã khóa</th>
                  <th>Lý do không hoàn thành</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
            <div class="modal-empty" id="incompletedEmpty" hidden>Chưa có dữ liệu phù hợp.</div>
          </div>
        </div>
      </div>
    </section>

    <section class="featured">
      <h2 class="section-title">Học viên tiêu biểu</h2>
      <div class="featured-grid">
        <div class="featured-column">
          <h3>Năm {{ date('Y') }}</h3>
          @if(!empty($featuredYear))
            <div class="card-stack">
              @foreach($featuredYear as $card)
                @php
                  $name = trim($card['name'] ?? '');
                  $initials = collect(preg_split('/\s+/u', $name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                  if ($initials === '') { $initials = 'HV'; }
                @endphp
                <article class="student-card">
                  <div class="student-avatar">
                    @if(!empty($card['avatar']))
                      <img src="{{ $card['avatar'] }}" alt="Ảnh học viên {{ $card['name'] }}">
                    @else
                      {{ $initials }}
                    @endif
                  </div>
                  <div class="student-info">
                    <div class="student-name">{{ $card['ms'] }} — {{ $card['name'] }}</div>
                    <div class="student-meta">{{ $card['position'] }}</div>
                    @if(!empty($card['company']) && $card['company'] !== '—')
                      <div class="student-meta">{{ $card['company'] }}</div>
                    @endif
                    @if(!empty($card['group']) && $card['group'] !== '—')
                      <div class="student-meta">{{ $card['group'] }}</div>
                    @endif
                    <div class="student-achievement">{{ $card['achievement'] }}</div>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <p class="empty-text">Chưa có dữ liệu trong năm hiện tại.</p>
          @endif
        </div>
        <div class="featured-column">
          <h3>Trong 3 tháng qua</h3>
          @if(!empty($featuredRecent))
            <div class="card-stack">
              @foreach($featuredRecent as $card)
                @php
                  $name = trim($card['name'] ?? '');
                  $initials = collect(preg_split('/\s+/u', $name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                  if ($initials === '') { $initials = 'HV'; }
                @endphp
                <article class="student-card">
                  <div class="student-avatar">
                    @if(!empty($card['avatar']))
                      <img src="{{ $card['avatar'] }}" alt="Ảnh học viên {{ $card['name'] }}">
                    @else
                      {{ $initials }}
                    @endif
                  </div>
                  <div class="student-info">
                    <div class="student-name">{{ $card['ms'] }} — {{ $card['name'] }}</div>
                    <div class="student-meta">{{ $card['position'] }}</div>
                    @if(!empty($card['company']) && $card['company'] !== '—')
                      <div class="student-meta">{{ $card['company'] }}</div>
                    @endif
                    @if(!empty($card['group']) && $card['group'] !== '—')
                      <div class="student-meta">{{ $card['group'] }}</div>
                    @endif
                    <div class="student-achievement">{{ $card['achievement'] }}</div>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <p class="empty-text">Chưa có dữ liệu trong 3 tháng qua.</p>
          @endif
        </div>
      </div>
    </section>
  </main>

  <footer>
    Copyright © {{ date('Y') }} nfuongtech.io.vn. All rights reserved.
  </footer>

  <div class="modal" id="registrationsModal" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-print-brand" aria-hidden="true">TRƯỜNG CAO ĐẲNG THACO</div>
        <h3 class="modal-title" id="modalTitle">Danh sách học viên</h3>
        <div class="modal-actions">
          <button type="button" class="btn btn-print" id="modalPrint">In</button>
          <button type="button" class="modal-close" data-modal-close aria-label="Đóng">×</button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="modal-table">
          <thead>
            <tr>
              <th>TT</th>
              <th>Mã số</th>
              <th class="left">Họ &amp; Tên</th>
              <th>Năm sinh</th>
              <th class="left">Chức vụ</th>
              <th class="left">Đơn vị</th>
            </tr>
          </thead>
          <tbody id="modalBody"></tbody>
        </table>
        <div class="modal-loader" id="modalLoader" hidden>Đang tải danh sách học viên...</div>
        <div class="modal-empty" id="modalEmpty" hidden>Chưa có học viên đăng ký.</div>
      </div>
    </div>
  </div>

  <script>
    const form  = document.getElementById('filterForm');
    const week  = document.getElementById('week');
    const month = document.getElementById('month');
    const year  = document.getElementById('year');

    function onWeekChange(){ month.value=''; year.value=''; form.submit(); }
    function onMonthChange(){ week.value=''; if(!year.value){ year.value=new Date().getFullYear().toString(); } form.submit(); }
    function onYearChange(){ week.value=''; month.value=''; form.submit(); }

    const modal = document.getElementById('registrationsModal');
    const modalBody = document.getElementById('modalBody');
    const modalLoader = document.getElementById('modalLoader');
    const modalEmpty = document.getElementById('modalEmpty');
    const modalTitle = document.getElementById('modalTitle');
    const modalCloseTriggers = modal.querySelectorAll('[data-modal-close]');
    const registrationButtons = document.querySelectorAll('[data-course]');
    const modalPrintButton = document.getElementById('modalPrint');

    if(modalPrintButton){
      modalPrintButton.addEventListener('click', () => window.print());
    }

    registrationButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const courseId = btn.dataset.course;
        if(!courseId) return;
        const courseName = btn.dataset.courseName || '';
        const courseSchedule = btn.dataset.courseSchedule || '';
        const courseLocation = btn.dataset.courseLocation || '';
        openRegistrationsModal(courseId, courseName, courseSchedule, courseLocation);
      });
    });

    function openRegistrationsModal(courseId, courseName, courseSchedule, courseLocation){
      const urlTemplate = document.body.dataset.registrationsUrl || '';
      if(!urlTemplate) return;
      const url = urlTemplate.replace('__ID__', encodeURIComponent(courseId));

      modal.hidden = false;
      document.body.classList.add('modal-open');
      const baseTitle = courseName ? `Danh sách học viên “${courseName}”` : 'Danh sách học viên';
      const metaParts = [];
      if(courseSchedule){ metaParts.push(courseSchedule); }
      if(courseLocation){ metaParts.push(courseLocation); }
      modalTitle.textContent = metaParts.length ? `${baseTitle} - ${metaParts.join(', ')}` : baseTitle;
      modalBody.innerHTML = '';
      modalLoader.hidden = false;
      modalEmpty.hidden = true;
      modalEmpty.textContent = 'Chưa có học viên đăng ký.';

      fetch(url)
        .then(res => {
          if(!res.ok){
            throw res;
          }
          return res.json();
        })
        .then(data => {
          modalLoader.hidden = true;
          const registrations = Array.isArray(data.registrations) ? data.registrations : [];
          if(!registrations.length){
            modalEmpty.hidden = false;
            return;
          }

          const fragment = document.createDocumentFragment();
          registrations.forEach(item => {
            const tr = document.createElement('tr');
            const cells = [
              { value: item.stt ?? '' },
              { value: item.ms ?? '—' },
              { value: item.ho_ten ?? '—', align: 'left' },
              { value: item.nam_sinh ?? '—' },
              { value: item.chuc_vu ?? '—', align: 'left' },
              { value: normalizeUnitText(item.don_vi), align: 'left' },
            ];

            cells.forEach(cell => {
              const td = document.createElement('td');
              if(cell.align === 'left'){
                td.classList.add('left');
              }
              const content = (cell.value !== undefined && cell.value !== null && cell.value !== '') ? cell.value : '—';
              td.textContent = content;
              tr.appendChild(td);
            });

            fragment.appendChild(tr);
          });

          modalBody.appendChild(fragment);
        })
        .catch(async error => {
          modalLoader.hidden = true;
          modalEmpty.hidden = false;
          modalEmpty.textContent = 'Không thể tải danh sách học viên.';
          if(error && typeof error.json === 'function'){
            try {
              const detail = await error.json();
              if(detail && detail.message){
                modalEmpty.textContent = detail.message;
              }
            } catch(_) {}
          }
        });
    }

    function closeModal(){
      modal.hidden = true;
      document.body.classList.remove('modal-open');
    }

    modalCloseTriggers.forEach(trigger => trigger.addEventListener('click', closeModal));
    document.addEventListener('keydown', event => {
      if(event.key === 'Escape' && !modal.hidden){
        closeModal();
      }
    });

    const lookupForm = document.getElementById('lookupForm');
    const lookupInput = document.getElementById('lookupInput');
    const lookupMessage = document.getElementById('lookupMessage');
    const lookupResults = document.getElementById('lookupResults');
    const completedBody = document.querySelector('#completedTable tbody');
    const incompletedBody = document.querySelector('#incompletedTable tbody');
    const completedEmpty = document.getElementById('completedEmpty');
    const incompletedEmpty = document.getElementById('incompletedEmpty');
    const lookupUrl = document.body.dataset.lookupUrl || '';

    function setLookupMessage(message){
      if(!lookupMessage) return;
      if(message && message.trim() !== ''){
        lookupMessage.textContent = message;
        lookupMessage.hidden = false;
      } else {
        lookupMessage.textContent = '';
        lookupMessage.hidden = true;
      }
    }

    setLookupMessage('');

    lookupForm.addEventListener('submit', event => {
      event.preventDefault();
      const query = lookupInput.value.trim();
      if(!query){
        setLookupMessage('Vui lòng nhập thông tin cần tra cứu.');
        if(lookupResults){
          lookupResults.hidden = true;
        }
        return;
      }

      setLookupMessage('Đang tra cứu...');
      completedBody.innerHTML = '';
      incompletedBody.innerHTML = '';
      completedEmpty.hidden = true;
      incompletedEmpty.hidden = true;
      if(lookupResults){
        lookupResults.hidden = false;
      }

      fetch(lookupUrl + '?q=' + encodeURIComponent(query))
        .then(res => {
          if(res.status === 422){
            return res.json().then(data => {
              throw new Error(data.message || 'Vui lòng kiểm tra thông tin nhập.');
            });
          }
          if(!res.ok){
            throw new Error('Không thể tra cứu kết quả.');
          }
          return res.json();
        })
        .then(data => {
          const completed = Array.isArray(data.completed) ? data.completed : [];
          const incompleted = Array.isArray(data.incompleted) ? data.incompleted : [];
          const hasResult = completed.length || incompleted.length;
          setLookupMessage(hasResult ? 'Đã cập nhật kết quả tra cứu.' : 'Không tìm thấy kết quả phù hợp.');
          renderLookupTable(completedBody, completedEmpty, completed, true);
          renderLookupTable(incompletedBody, incompletedEmpty, incompleted, false);
        })
        .catch(error => {
          setLookupMessage(error.message || 'Không thể tra cứu kết quả.');
          completedEmpty.hidden = false;
          incompletedEmpty.hidden = false;
        });
    });

    function renderLookupTable(body, emptyEl, items, isCompleted){
      body.innerHTML = '';
      if(!Array.isArray(items) || !items.length){
        emptyEl.hidden = false;
        return;
      }
      emptyEl.hidden = true;

      const fragment = document.createDocumentFragment();
      items.forEach(item => {
        const tr = document.createElement('tr');
        if(isCompleted){
          const cells = [
            item.stt ?? '',
            item.ms ?? '—',
            item.ho_ten ?? '—',
            item.cong_ty ?? '—',
            item.thaco ?? '—',
            item.ten_khoa_hoc ?? '—',
            item.ma_khoa ?? '—',
            formatScore(item.dtb),
            formatHours(item.gio_thuc_hoc),
            item.ngay_hoan_thanh ?? '—',
            formatCurrency(item.chi_phi),
            item.chung_nhan ? { href: item.chung_nhan, label: 'Chứng nhận' } : '—'
          ];
          cells.forEach(cell => tr.appendChild(createCell(cell)));
        } else {
          const cells = [
            item.stt ?? '',
            item.ms ?? '—',
            item.ho_ten ?? '—',
            item.cong_ty ?? '—',
            item.thaco ?? '—',
            item.ten_khoa_hoc ?? '—',
            item.ma_khoa ?? '—',
            item.ly_do ?? '—'
          ];
          cells.forEach(cell => tr.appendChild(createCell(cell)));
        }
        fragment.appendChild(tr);
      });
      body.appendChild(fragment);
    }

    function createCell(cell){
      const td = document.createElement('td');
      if(cell && typeof cell === 'object' && cell.href){
        const link = document.createElement('a');
        link.href = cell.href;
        link.textContent = cell.label || cell.href;
        link.target = '_blank';
        link.rel = 'noopener';
        td.appendChild(link);
      } else {
        td.textContent = (cell !== undefined && cell !== null && cell !== '') ? cell : '—';
      }
      return td;
    }

    function normalizeUnitText(value){
      if(value === undefined || value === null){
        return '—';
      }

      const stringValue = String(value).trim();
      if(stringValue === ''){
        return '—';
      }

      const replaced = stringValue.replace(/\s*•\s*/g, ', ');
      const collapsed = replaced.replace(/\s{2,}/g, ' ').trim();

      return collapsed !== '' ? collapsed : '—';
    }

    function formatScore(value){
      const number = parseFloat(value);
      if(Number.isFinite(number)){
        return number.toFixed(number % 1 === 0 ? 0 : 1);
      }
      return '—';
    }

    function formatHours(value){
      const number = parseFloat(value);
      if(Number.isFinite(number)){
        const options = number % 1 === 0 ? { minimumFractionDigits:0, maximumFractionDigits:0 } : { minimumFractionDigits:1, maximumFractionDigits:1 };
        return number.toLocaleString('vi-VN', options);
      }
      return '—';
    }

    function formatCurrency(value){
      const number = parseFloat(value);
      if(Number.isFinite(number)){
        return number.toLocaleString('vi-VN') + ' đ';
      }
      return '—';
    }
  </script>
</body>
</html>
