<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Kế hoạch đào tạo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --border:#e5e7eb;
      --text:#111827;
      --muted:#6b7280;
      --brand:#111827;
      --accent:#2563eb;
      --today-bg:#dcfce7;
    }
    html,body{height:100%}
    body{
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji";
      margin:20px; display:flex; flex-direction:column; min-height:100vh; color:var(--text);
    }

    /* Header */
    .topbar{display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px; flex-wrap:wrap}
    .brand{font-weight:800; letter-spacing:.3px; font-size:150%; white-space:nowrap;} /* luôn 1 hàng */
    .brand a{text-decoration:none; color:var(--brand)}

    /* Login form */
    .login-wrap{
      display:flex; flex-direction:column; gap:8px;
      align-items:stretch; /* mobile: căn trái */
      width:100%; max-width:680px;
    }
    .login-form{
      display:grid;
      grid-template-columns:1fr;   /* mobile: 1 cột */
      grid-auto-rows:auto;
      gap:8px;
      width:100%;
    }
    .login-row{min-width:0;}
    .login-row input{
      width:100%; box-sizing:border-box;
      padding:8px 10px; border:1px solid var(--border); border-radius:6px;
    }

    /* Căn phải cho Ghi nhớ + Đăng nhập */
    .login-actions{
      display:flex; flex-wrap:wrap; gap:10px; align-items:center;
      justify-content:flex-end;   /* căn phải trên mobile & PC */
      width:100%;
    }
    .remember{display:flex; align-items:center; gap:6px; font-size:14px; color:#374151}

    .btn{padding:8px 12px; border-radius:6px; border:1px solid var(--border); background:#fff; color:var(--text); cursor:pointer}
    .btn-primary{background:var(--accent); color:#fff; border-color:var(--accent)}

    /* PC ≥ 768px: user|pass cùng dòng; dưới: actions (remember + login) cùng dòng, căn phải */
    @media (min-width:768px){
      .login-wrap{align-items:flex-end;}
      .login-form{
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
          "user pass"
          "actions actions";
        column-gap:10px; row-gap:8px;
      }
      .login-user{grid-area:user}
      .login-pass{grid-area:pass}
      .login-actions{grid-area:actions; justify-content:flex-end;}
    }

    /* Filters & table */
    .filters{margin:12px 0 16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap}
    .filters label select{padding:6px 8px}
    .table-wrap{width:100%; overflow-x:auto}
    table{min-width:780px; width:100%; border-collapse:collapse; background:#fff}
    /* Header: nền xám mặc định + căn giữa */
    th{
      background:#f3f4f6; /* xám mặc định */
      text-align:center;
      border:1px solid var(--border);
      padding:8px 10px;
      vertical-align:middle; /* căn giữa theo hàng */
    }
    /* Ô dữ liệu: căn giữa theo hàng; text căn giữa mặc định, riêng cột left thì căn trái */
    td{
      border:1px solid var(--border);
      padding:8px 10px;
      vertical-align:middle; /* căn giữa theo hàng */
      text-align:center;     /* mặc định căn giữa theo cột */
    }
    td.left{ text-align:left; } /* giữ nguyên cột Tên khóa học căn trái */

    .badge{display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; color:var(--text)}
    .badge.gray{background:#e5e7eb}
    .badge.info{background:#dbeafe}
    .badge.warn{background:#fef3c7}
    .badge.ok{background:#dcfce7}
    .nowrap{white-space:nowrap}
    .session.today{
      /* không in đậm, vẫn nền xanh nhạt */
      background:var(--today-bg); padding:1px 6px; border-radius:6px;
    }
    footer{margin-top:auto; padding-top:14px; color:var(--muted); font-size:14px; text-align:center}

    /* Màn hình rất hẹp */
    @media (max-width:420px){
      .brand{font-size:135%}
      table{min-width:720px}
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="brand"><a href="/">QUẢN LÝ ĐÀO TẠO</a></div>

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
          <!-- Hàng 1 (PC): user | pass  — Mobile: 2 hàng -->
          <div class="login-row login-user">
            <input type="text" name="user" value="{{ old('user') }}" placeholder="User / Email" required>
          </div>
          <div class="login-row login-pass">
            <input type="password" name="password" placeholder="Password" required>
          </div>

          <!-- Hàng 2: Ghi nhớ + Đăng nhập (căn phải) -->
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

  <h2>Kế hoạch đào tạo</h2>

  {{-- 3 filter riêng lẻ: Tuần -> Tháng -> Năm (giữ logic đã thống nhất) --}}
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
    <table>
      <thead>
        <tr>
          <th class="nowrap">TT</th>
          <th class="nowrap">Mã khóa</th>
          <th>Tên khóa học</th>
          <th>Giảng viên</th>
          <th>Ngày, Giờ đào tạo</th>
          <th>Địa điểm</th>
          <th class="nowrap">Tuần</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $i => $r)
          <tr>
            <td class="nowrap">{{ $i+1 }}</td>
            <td class="nowrap">{{ $r['ma_khoa_hoc'] }}</td>
            <td class="left">{{ $r['ten_khoa_hoc'] }}</td> {{-- chỉ cột này căn trái --}}
            <td>{{ $r['giang_vien'] }}</td>
            <td>{!! $r['ngay_gio_html'] !!}</td>
            <td>{!! $r['dia_diem_html'] !!}</td>
            <td class="nowrap">{{ $r['tuan'] }}</td>
            <td>
              @php $classes = ['Dự thảo'=>'gray','Ban hành'=>'info','Đang đào tạo'=>'warn','Kết thúc'=>'ok']; @endphp
              <span class="badge {{ $classes[$r['trang_thai']] ?? 'gray' }}">{{ $r['trang_thai'] }}</span>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" style="text-align:center; color:var(--muted)">Không có dữ liệu.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <footer>
    Copyright © {{ date('Y') }} nfuongtech.io.vn. All rights reserved.
  </footer>

  <script>
    const form  = document.getElementById('filterForm');
    const week  = document.getElementById('week');
    const month = document.getElementById('month');
    const year  = document.getElementById('year');

    function onWeekChange(){ month.value=''; year.value=''; form.submit(); }
    function onMonthChange(){ week.value=''; if(!year.value){ year.value=new Date().getFullYear().toString(); } form.submit(); }
    function onYearChange(){ week.value=''; month.value=''; form.submit(); }
  </script>
</body>
</html>
