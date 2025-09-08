<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Học viên</label>
                <select wire:model="hoc_vien_id" class="w-full rounded border-gray-300">
                    <option value="">-- Chọn học viên --</option>
                    @foreach($this->hocViens as $id => $ten)
                        <option value="{{ $id }}">{{ $ten }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Khóa học</label>
                <select wire:model="khoa_hoc_id" class="w-full rounded border-gray-300">
                    <option value="">-- Chọn khóa học --</option>
                    @foreach($this->khoaHocs as $id => $ten)
                        <option value="{{ $id }}">{{ $ten }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($hoc_vien_id && $khoa_hoc_id)
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Điểm tổng kết</label>
                    <input type="number" step="0.01" wire:model="diem_tong_ket" class="w-full rounded border-gray-300">
                </div>

                <div>
                    <label>Kết quả</label>
                    <select wire:model="ket_qua" class="w-full rounded border-gray-300">
                        <option value="">-- Chọn kết quả --</option>
                        <option value="Hoàn thành">Hoàn thành</option>
                        <option value="Không hoàn thành">Không hoàn thành</option>
                        <option value="Đạt yêu cầu">Đạt yêu cầu</option>
                        <option value="Không đạt yêu cầu">Không đạt yêu cầu</option>
                        <option value="Vắng (Phép)">Vắng (Phép)</option>
                        <option value="Vắng (Không phép)">Vắng (Không phép)</option>
                    </select>
                </div>
            </div>

            <div>
                <label>Học phí (VNĐ)</label>
                <input type="number" wire:model="hoc_phi" class="w-full rounded border-gray-300">
            </div>

            <h3 class="text-lg font-semibold mt-4">Kết quả từng chuyên đề</h3>
            <table class="min-w-full border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">Chuyên đề</th>
                        <th class="border p-2">Điểm</th>
                        <th class="border p-2">Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chuyen_de_data as $index => $item)
                        <tr>
                            <td class="border p-2">{{ $item['ten_chuyen_de'] }}</td>
                            <td class="border p-2">
                                <input type="number" step="0.01" wire:model="chuyen_de_data.{{ $index }}.diem" class="w-full rounded border-gray-300">
                            </td>
                            <td class="border p-2">
                                <select wire:model="chuyen_de_data.{{ $index }}.ket_qua" class="w-full rounded border-gray-300">
                                    <option value="">-- Chọn --</option>
                                    <option value="Hoàn thành">Hoàn thành</option>
                                    <option value="Không hoàn thành">Không hoàn thành</option>
                                    <option value="Đạt yêu cầu">Đạt yêu cầu</option>
                                    <option value="Không đạt yêu cầu">Không đạt yêu cầu</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Lưu kết quả</button>
            </div>
        @endif
    </form>
</x-filament::page>
