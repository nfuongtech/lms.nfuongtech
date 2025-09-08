<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\EmailTemplate;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Mail\GenericTemplateMailable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Mail;

class DangKiesRelationManager extends RelationManager
{
    protected static string $relationship = 'dangKies';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('hoc_vien_id')
                ->relationship('hocVien', 'ho_ten')
                ->required(),
            Forms\Components\Select::make('trang_thai')
                ->label('Trạng thái đăng ký')
                ->options([
                    'dang_cho' => 'Đang chờ',
                    'da_duoc_duyet' => 'Đã duyệt',
                    'huy' => 'Hủy',
                ])->default('dang_cho'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Học viên'),
                Tables\Columns\TextColumn::make('hocVien.email')->label('Email'),
                Tables\Columns\TextColumn::make('trang_thai')->label('Tình trạng'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày đăng ký')->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('send_to_all')
                    ->label('Gửi email cho tất cả HV')
                    ->form([
                        Forms\Components\Select::make('email_template_id')
                            ->label('Mẫu email')
                            ->options(EmailTemplate::pluck('ten_mau','id')->toArray())
                            ->required(),
                        Forms\Components\Select::make('email_account_id')
                            ->label('Tài khoản gửi (tùy chọn)')
                            ->options(EmailAccount::pluck('name','id')->toArray())
                            ->nullable(),
                        Forms\Components\CheckboxList::make('statuses')
                            ->label('Lọc theo trạng thái đăng ký')
                            ->options([
                                'dang_cho' => 'Đang chờ',
                                'da_duoc_duyet' => 'Đã duyệt',
                                'huy' => 'Hủy',
                            ]),
                    ])
                    ->action(function (array $data, $livewire) {
                        $ownerRecord = $livewire->getOwnerRecord();
                        $template = EmailTemplate::find($data['email_template_id']);
                        if (!$template) {
                            throw new \Exception('Mẫu email không tồn tại');
                        }

                        $query = $ownerRecord->dangKies()->with('hocVien');
                        if (!empty($data['statuses'])) {
                            $query->whereIn('trang_thai', $data['statuses']);
                        }
                        $dangKies = $query->get();

                        foreach ($dangKies as $dk) {
                            $hv = $dk->hocVien;
                            if (!$hv || empty($hv->email)) continue;

                            try {
                                Mail::to($hv->email)->send(
                                    new GenericTemplateMailable($template->tieu_de, $template->noi_dung)
                                );

                                EmailLog::create([
                                    'khoa_hoc_id' => $ownerRecord->id,
                                    'recipient_email' => $hv->email,
                                    'subject' => $template->tieu_de,
                                    'content' => $template->noi_dung,
                                    'status' => 'sent',
                                    'email_account_id' => $data['email_account_id'] ?? null,
                                ]);
                            } catch (\Exception $e) {
                                EmailLog::create([
                                    'khoa_hoc_id' => $ownerRecord->id,
                                    'recipient_email' => $hv->email,
                                    'subject' => $template->tieu_de,
                                    'content' => $template->noi_dung,
                                    'status' => 'failed',
                                    'error_message' => $e->getMessage(),
                                    'email_account_id' => $data['email_account_id'] ?? null,
                                ]);
                            }
                        }

                        return \Filament\Notifications\Notification::make()
                            ->title('Hoàn tất gửi email')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
