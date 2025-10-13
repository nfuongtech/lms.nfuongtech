<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Exports\SimpleArrayExport;
use App\Filament\Resources\HocVienHoanThanhResource;
use App\Mail\PlanNotificationMail;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Xuất Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->extraAttributes(['style' => 'background-color:#CCFFD8;color:#00529C;'])
                ->action(function () {
                    $records = $this->getExportCollection();

                    if ($records->isEmpty()) {
                        Notification::make()->title('Không có dữ liệu để xuất')->warning()->send();
                        return null;
                    }

                    return HocVienHoanThanhResource::export($records, 'hoc_vien_hoan_thanh.xlsx');
                }),

            Actions\Action::make('download_template')
                ->label('Tải mẫu import')
                ->icon('heroicon-o-arrow-down-on-square')
                ->extraAttributes(['style' => 'background-color:#CCFFD8;color:#00529C;'])
                ->action(function () {
                    $headings = [
                        'MS',
                        'Họ & Tên',
                        'Tên khóa học',
                        'Mã khóa',
                        'ĐTB',
                        'Giờ thực học',
                        'Ngày hoàn thành',
                        'Chi phí đào tạo',
                        'Link chứng nhận',
                        'Thời hạn chứng nhận (năm)',
                        'Ngày hết hạn chứng nhận',
                        'Ghi chú',
                    ];

                    return Excel::download(new SimpleArrayExport([], $headings), 'mau_hoc_vien_hoan_thanh.xlsx');
                }),

            Actions\Action::make('import_excel')
                ->label('Import học viên hoàn thành')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Chọn file Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    $file = $data['file'] ?? null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()->title('Không tìm thấy file tải lên')->danger()->send();
                        return;
                    }

                    $rows = HocVienHoanThanhResource::parseImportRows($file->getRealPath());

                    if (empty($rows)) {
                        Notification::make()->title('File không có dữ liệu hợp lệ')->warning()->send();
                        return;
                    }

                    $imported = 0;
                    $errors = [];

                    foreach ($rows as $index => $row) {
                        $result = HocVienHoanThanhResource::handleImportRow($row);
                        if (! empty($result['errors'])) {
                            foreach ($result['errors'] as $message) {
                                $errors[] = 'Dòng ' . ($index + 2) . ': ' . $message;
                            }
                            continue;
                        }

                        $imported++;
                    }

                    if (! empty($errors)) {
                        Notification::make()
                            ->title('Có lỗi khi import')
                            ->body(implode("\n", array_slice($errors, 0, 10)))
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('Đã import dữ liệu')
                        ->body('Số dòng cập nhật: ' . $imported . (empty($errors) ? '' : '. Có ' . count($errors) . ' lỗi.'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('send_email')
                ->label('Gửi Email')
                ->icon('heroicon-o-envelope')
                ->extraAttributes(['style' => 'background-color:#FFFCD5;color:#00529C;'])
                ->form([
                    Forms\Components\Select::make('email_template_id')
                        ->label('Mẫu email')
                        ->options(fn () => EmailTemplate::orderBy('ten_mau')->pluck('ten_mau', 'id')->toArray())
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('email_account_id')
                        ->label('Tài khoản gửi')
                        ->options(fn () => EmailAccount::where('active', 1)->orderBy('name')->pluck('name', 'id')->toArray())
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $records = $this->getExportCollection();

                    if ($records->isEmpty()) {
                        Notification::make()->title('Không có học viên để gửi email')->warning()->send();
                        return;
                    }

                    $template = EmailTemplate::find($data['email_template_id'] ?? null);
                    $account = EmailAccount::find($data['email_account_id'] ?? null);

                    if (! $template || ! $account) {
                        Notification::make()->title('Thiếu thông tin gửi email')->danger()->send();
                        return;
                    }

                    Config::set('mail.mailers.dynamic', [
                        'transport' => 'smtp',
                        'host' => $account->host,
                        'port' => $account->port,
                        'encryption' => $account->encryption_tls ? 'tls' : null,
                        'username' => $account->username,
                        'password' => $account->password,
                    ]);

                    Config::set('mail.from', [
                        'address' => $account->email,
                        'name' => $account->name,
                    ]);

                    $success = 0;
                    $failed = 0;

                    foreach ($records as $record) {
                        $hocVien = $record->hocVien;
                        $ketQua = $record->ketQua;
                        $course = $record->khoaHoc;

                        $recipient = $hocVien?->email;
                        if (! $recipient) {
                            $failed++;
                            EmailLog::create([
                                'email_account_id' => $account->id,
                                'recipient_email' => 'N/A',
                                'subject' => 'Không gửi (thiếu email học viên)',
                                'content' => '',
                                'status' => 'failed',
                                'error_message' => 'Học viên không có email.',
                            ]);
                            continue;
                        }

                        $placeholders = [
                            '{ten_hoc_vien}' => $hocVien?->ho_ten ?? 'N/A',
                            '{msnv}' => $hocVien?->msnv ?? 'N/A',
                            '{ten_khoa_hoc}' => $course?->ten_khoa_hoc ?? 'N/A',
                            '{ma_khoa_hoc}' => $course?->ma_khoa_hoc ?? 'N/A',
                            '{diem_tb}' => $ketQua?->diem_trung_binh ? number_format((float) $ketQua->diem_trung_binh, 1, '.', '') : '-',
                            '{gio_thuc_hoc}' => $ketQua?->tong_so_gio_thuc_te ? number_format((float) $ketQua->tong_so_gio_thuc_te, 1, '.', '') : '-',
                            '{ket_qua}' => $ketQua && $ketQua->ket_qua === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành',
                        ];

                        $subject = strtr($template->tieu_de, $placeholders);
                        $body = strtr($template->noi_dung, $placeholders);

                        try {
                            Mail::mailer('dynamic')->to($recipient)->send(new PlanNotificationMail($subject, $body));
                            $success++;
                            $status = 'success';
                            $error = null;
                        } catch (\Throwable $exception) {
                            $failed++;
                            $status = 'failed';
                            $error = $exception->getMessage();
                            Log::error('Lỗi gửi email học viên hoàn thành: ' . $exception->getMessage());
                        }

                        EmailLog::create([
                            'email_account_id' => $account->id,
                            'recipient_email' => $recipient,
                            'subject' => $subject,
                            'content' => $body,
                            'status' => $status,
                            'error_message' => $error,
                        ]);
                    }

                    Notification::make()
                        ->title('Gửi email hoàn tất')
                        ->body("Thành công: {$success}. Thất bại: {$failed}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getExportCollection(): Collection
    {
        $query = clone $this->getTableQuery();

        return $query->get();
    }
}
