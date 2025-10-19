<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\HocVienImport;
use App\Exports\HocVienExport;
use App\Exports\MauNhapHocVienExport;
use App\Models\DonVi;
use App\Models\DonViPhapNhan;
use App\Models\TuyChonKetQua;
use Illuminate\Support\HtmlString;

class ListHocViens extends ListRecords
{
    protected static string $resource = HocVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('tai_file_mau')
                ->label('Tải file mẫu')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->extraAttributes(['class' => 'text-black bg-white'])
                ->action(fn () => Excel::download(new MauNhapHocVienExport, 'mau_nhap_hoc_vien.xlsx')),

            Actions\Action::make('nhap_excel')
                ->label('Nhập Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->extraAttributes(['class' => 'text-black bg-white'])
                ->form([
                    FileUpload::make('file')
                        ->label('Chọn file Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->storeFiles(false),
                    Radio::make('on_duplicate')
                        ->label('Khi gặp bản ghi trùng (msnv hoặc email)')
                        ->options([
                            'skip' => 'Bỏ qua bản ghi trùng (giữ bản ghi hiện có)',
                            'update' => 'Cập nhật bản ghi trùng (ghi đè bằng dữ liệu file)',
                        ])
                        ->default('skip'),
                ])
                ->action(function (array $data) {
                    $file = $data['file'] ?? null;
                    $mode = $data['on_duplicate'] ?? 'skip';

                    if (!$file) {
                        Notification::make()->title('Không có file')->danger()->send();
                        return;
                    }

                    $import = new HocVienImport($mode);

                    try {
                        Excel::import($import, $file);

                        $report = $import->getReport();
                        $errors = $import->getErrors();

                        $errorFilePath = null;
                        if (!empty($errors)) {
                            $errorFilePath = $import->report();
                        }

                        $msg = "Nhập xong: Thêm mới {$report['inserted']}, Cập nhật {$report['updated']}, Bỏ qua {$report['skipped']}, Lỗi {$report['errors']}";

                        $notification = Notification::make()
                            ->title('Kết quả nhập Excel')
                            ->success()
                            ->body($msg);

                        if ($errorFilePath) {
                            $url = route('download.import.errors', ['path' => $errorFilePath]);
                            $link = "<a href=\"{$url}\" target=\"_blank\" style=\"color: #1d4ed8; text-decoration: underline;\">Tải file lỗi</a>";
                            $notification->body(new HtmlString($msg . '<br>Chi tiết lỗi: ' . $link));
                        }

                        $notification->send();

                        return redirect($this->getResource()::getUrl('index'));
                    } catch (\Maatwebsite\Excel\Validators\ValidationException $ve) {
                        $failures = collect($ve->failures())->map(function ($f) {
                            return sprintf('Hàng %d: %s', $f->row(), implode('; ', $f->errors()));
                        })->implode("\n");

                        Notification::make()
                            ->title('Lỗi khi nhập dữ liệu')
                            ->body($failures)
                            ->danger()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Lỗi khi nhập dữ liệu')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        \Log::error("Lỗi nhập Excel HocVien: " . $e->getMessage());
                    }
                }),

            Actions\Action::make('xuat_excel')
                ->label('Xuất Excel')
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->color('gray')
                ->extraAttributes(['class' => 'text-black bg-white'])
                ->action(fn () => Excel::download(new HocVienExport($this->getTableQuery()), 'danh_sach_hoc_vien.xlsx')),

            // Đặt Tạo học viên cuối cùng
            Actions\CreateAction::make()->label('Tạo học viên'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('don_vi.thaco_tdtv')
                ->label('THACO/TĐTV')
                ->relationship('donVi', 'thaco_tdtv')
                ->searchable()
                ->preload(),

            SelectFilter::make('don_vi.cong_ty_ban_nvqt')
                ->label('Công ty/Ban NVQT')
                ->relationship('donVi', 'cong_ty_ban_nvqt')
                ->searchable()
                ->preload(),

            SelectFilter::make('don_vi_phap_nh an_id')
                ->label('Đơn vị pháp nhân')
                ->relationship('donViPhapNhan', 'ten_don_vi')
                ->searchable()
                ->preload(),

            SelectFilter::make('tinh_trang')
                ->label('Tình trạng')
                ->options(fn () => TuyChonKetQua::where('loai', 'tinh_trang_hoc_vien')->pluck('gia_tri', 'gia_tri')->toArray())
                ->searchable()
                ->preload(),
        ];
    }
}
