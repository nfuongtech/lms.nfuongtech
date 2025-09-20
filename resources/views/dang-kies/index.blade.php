<!-- file: resources/views/dang-kies/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ghi danh khóa học - Thông minh</h2>

    <div class="mb-4">
        <form id="filterForm" class="form-inline">
            <label for="filterType">Lọc theo:</label>
            <select id="filterType" name="filterType" class="form-control mx-2">
                <option value="week" {{ $filterType == 'week' ? 'selected' : '' }}>Tuần</option>
                <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Tháng</option>
            </select>

            <div id="weekGroup" style="{{ $filterType == 'week' ? '' : 'display:none' }}">
                <label>Tuần:</label>
                <input name="week" type="number" value="{{ $week }}" class="form-control mx-1" min="1" max="53">
                <input name="year" type="number" value="{{ $year }}" class="form-control mx-1" min="2000">
            </div>

            <div id="monthGroup" style="{{ $filterType == 'month' ? '' : 'display:none' }}">
                <label>Tháng:</label>
                <input name="month" type="number" value="{{ $month }}" class="form-control mx-1" min="1" max="12">
                <input name="year" type="number" value="{{ $year }}" class="form-control mx-1" min="2000">
            </div>

            <button class="btn btn-primary" type="submit">Áp dụng</button>
        </form>
    </div>

    <div class="row">
        <!-- Block 1: chuyen_de & counts -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Danh sách chuyên đề (kèm số lượng học viên đã ghi danh)</div>
                <div class="card-body" style="max-height:450px; overflow:auto;">
                    <table class="table table-sm">
                        <thead><tr><th>Chuyên đề</th><th>Mã</th><th>Số HV</th></tr></thead>
                        <tbody>
                        @foreach($chuyenDes as $cd)
                            <tr>
                                <td>{{ $cd->ten_chuyen_de }}</td>
                                <td>{{ $cd->ma_so }}</td>
                                <td>{{ $cd->hoc_vien_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Block 2: danh sách dang_kies -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Danh sách học viên đã ghi danh (đã lọc)</div>
                <div class="card-body" style="max-height:450px; overflow:auto;">
                    <table class="table table-sm">
                        <thead><tr><th>MSNV</th><th>Họ tên</th><th>Đơn vị</th><th>Khoa học</th><th>Ngày ghi danh</th></tr></thead>
                        <tbody id="registeredList">
                        @foreach($dangKies as $dk)
                            <tr>
                                <td>{{ optional($dk->hocVien)->msnv }}</td>
                                <td>{{ optional($dk->hocVien)->ho_ten }}</td>
                                <td>{{ optional(optional($dk->hocVien)->donVi)->ma_don_vi ?? '-' }}</td>
                                <td>{{ optional($dk->khoaHoc)->ma_khoa_hoc }}</td>
                                <td>{{ $dk->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $dangKies->links() }}
                </div>
            </div>

            <!-- Ghi danh form -->
            <div class="card">
                <div class="card-header">Ghi danh (dán MSNV vào ô bên dưới)</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Chọn Khóa học</label>
                        <select id="khoaHocSelect" class="form-control">
                            <option value="">-- Chọn khóa học --</option>
                            @foreach(\App\Models\KhoaHoc::all() as $kh)
                                <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} ({{ $kh->trang_thai }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Paste MSNV (ngăn cách bằng dấu phẩy)</label>
                        <textarea id="msnvPaste" class="form-control" rows="3" placeholder="HV01,HV02,..."></textarea>
                        <small class="text-muted">Có thể dán nhiều MSNV, hệ thống sẽ tra và hiển thị thông tin.</small>
                    </div>

                    <div class="form-group">
                        <button id="btnPreview" class="btn btn-secondary">Xem trước</button>
                        <button id="btnSubmit" class="btn btn-success">Ghi danh</button>
                    </div>

                    <div id="previewArea"></div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const filterTypeEl = document.getElementById('filterType');
    const weekGroup = document.getElementById('weekGroup');
    const monthGroup = document.getElementById('monthGroup');
    filterTypeEl.addEventListener('change', function(){
        if (this.value === 'week'){ weekGroup.style.display=''; monthGroup.style.display='none'; }
        else { weekGroup.style.display='none'; monthGroup.style.display=''; }
    });

    document.getElementById('filterForm').addEventListener('submit', function(e){
        e.preventDefault();
        this.submit();
    });

    document.getElementById('btnPreview').addEventListener('click', function(){
        const msnv = document.getElementById('msnvPaste').value;
        fetch("{{ route('dang-kies.lookup') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({ msnv })
        }).then(r=>r.json()).then(data=>{
            const area = document.getElementById('previewArea');
            area.innerHTML = '';
            if (data.data && data.data.length){
                const list = document.createElement('ul');
                data.data.forEach(hv=>{
                    const li = document.createElement('li');
                    li.textContent = hv.label + ' ';
                    const chk = document.createElement('input');
                    chk.type='checkbox'; chk.checked=true; chk.value=hv.msnv; chk.name='msnv[]';
                    li.prepend(chk);
                    list.appendChild(li);
                });
                area.appendChild(list);
            } else {
                area.innerHTML = '<div class="text-warning">Không tìm thấy MSNV hợp lệ (hoặc không "Đang làm việc").</div>';
            }
        });
    });

    document.getElementById('btnSubmit').addEventListener('click', function(){
        const msnv = document.getElementById('msnvPaste').value;
        const khoa_hoc_id = document.getElementById('khoaHocSelect').value;
        if (!khoa_hoc_id) return alert('Chọn khóa học trước khi ghi danh.');

        // try to build array from preview checkboxes if exist, else fallback to pasted list
        const previewChecks = document.querySelectorAll('#previewArea input[type=checkbox]:checked');
        let msnv_list = [];
        if (previewChecks.length){
            previewChecks.forEach(c=> msnv_list.push(c.value));
        } else {
            msnv_list = msnv.split(',').map(x=>x.trim()).filter(x=>x);
        }

        fetch("{{ route('dang-kies.store') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({ khoa_hoc_id, msnv_list })
        }).then(r=>r.json()).then(data=>{
            if (data.status === 'ok'){
                alert('Hoàn tất. Kết quả: ' + JSON.stringify(data.results));
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Xảy ra lỗi'));
            }
        }).catch(e=>{
            alert('Lỗi server: '+e);
        });
    });

});
</script>
@endsection
