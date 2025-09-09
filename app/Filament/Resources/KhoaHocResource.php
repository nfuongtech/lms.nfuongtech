<?php

namespace App\Filament\Resources;

// use App\Enums\TrangThaiKhoaHoc; // Comment nếu không dùng Enum
use App\Filament\Resources\KhoaHocResource\Pages;
// use App\Filament\Resources\KhoaHocResource\RelationManagers; // Bỏ comment nếu có
use App\Models\ChuongTrinh;
use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc; // Dùng trong validate trùng giờ
use App\Models\QuyTacMaKhoa;
// Thêm model cho gửi email (nếu bạn có)
use App\Models\EmailTemplate;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// use Filament\Notifications\Notification; // Nếu dùng thông báo
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail; // Để gửi email

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Khóa học';
    protected static ?string $navigationGroup = 'Đào tạo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        // --- Bắt đầu thêm: Placeholder hiển thị tên khóa học ---
                        Forms\Components\Placeholder::make('ten_va_ma_placeholder')
                            ->label('Tên Khóa học')
                            ->content(fn ($record) => $record ? (($record->chuongTrinh?->ten_chuong_trinh ?? 'N/A') . ', ' . $record->ma_khoa_hoc) : '-'),
                        // --- Kết thúc thêm ---

                        Forms\Components\Select::make('chuong_trinh_id')
                            ->label('Chương trình')
                            ->options(function () {
                                return ChuongTrinh::where('tinh_trang', 'Đang áp dụng')->pluck('ten_chuong_trinh', 'id')->toArray();
                            })
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $ct = ChuongTrinh::find($state);
                                    if ($ct) {
                                        try {
                                            if (class_exists(QuyTacMaKhoa::class)) {
                                                $loaiHinhDaoTao = $ct->loai_hinh_dao_tao ?? null;
                                                if ($loaiHinhDaoTao) {
                                                    $set('ma_khoa_hoc', QuyTacMaKhoa::taoMaKhoaHoc($loaiHinhDaoTao));
                                                } else {
                                                     $set('ma_khoa_hoc', 'Tự động tạo khi lưu');
                                                }
                                            } else {
                                                $set('ma_khoa_hoc', 'Tự động tạo khi lưu');
                                            }
                                        } catch (\Throwable $e) {
                                            \Log::error('Lỗi tạo mã khóa học trong afterStateUpdated: ' . $e->getMessage());
                                            $set('ma_khoa_hoc', 'Tự động tạo khi lưu');
                                        }
                                    }
                                }
                            }),

                        // --- Giữ nguyên dehydrated(true) ---
                        Forms\Components\TextInput::make('ma_khoa_hoc')
                            ->label('Mã khóa học')
                            ->disabled() // Không cho sửa trực tiếp
                            ->dehydrated(true) // Cho phép gửi giá trị lên server (sẽ bị override)
                            ->required(),

                        Forms\Components\TextInput::make('nam')
                            ->label('Năm')
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->default(date('Y'))
                            ->required(),

                        // Thay thế Enum bằng options string nếu không dùng Enum
                        Forms\Components\Select::make('trang_thai')
                            ->label('Trạng thái')
                            ->options([
                                'Soạn thảo' => 'Soạn thảo',
                                'Kế hoạch' => 'Kế hoạch',
                                'Ban hành' => 'Ban hành',
                                'Đang đào tạo' => 'Đang đào tạo',
                                'Kết thúc' => 'Kết thúc',
                            ])
                            ->default('Kế hoạch')
                            ->required(),

                        Forms\Components\Toggle::make('gui_email')
                            ->label('Gửi email khi lưu')
                            ->default(false),
                    ])
                    ->columns(2),

                // --- PHẦN LỊCH HỌC ĐÃ SỬA THEO YÊU CẦU ---
                Forms\Components\Section::make('Lịch học')
                    ->schema([
                        Forms\Components\Repeater::make('lichHocs')
                            // --- BẮT BUỘC: Thêm relationship để Filament biết cách lưu ---
                            ->relationship('lichHocs')
                            ->label('Buổi học')
                            // --- Cập nhật hiển thị trực quan hơn ---
                            ->itemLabel(fn (array $state): ?string => isset($state['ngay_hoc']) ? 'Ngày ' . ($state['ngay_hoc'] ?? '...') . ' tại ' . ($state['dia_diem'] ?? '...') : null)
                            ->collapsible() // Cho phép thu gọn/mở rộng từng buổi
                            // ->collapsed() // Bỏ comment nếu muốn mặc định thu gọn
                            ->cloneable()
                            ->reorderable()
                            ->grid(2) // Hiển thị 2 cột item
                            ->addActionLabel('Thêm buổi học')
                            // --- Hết cập nhật hiển thị ---
                            ->schema([
                                // --- Hàng 1: Ngày học & Địa điểm ---
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\DatePicker::make('ngay_hoc')
                                        ->label('Ngày học')
                                        ->required()
                                        ->minDate(now())
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $date = \Carbon\Carbon::parse($state);
                                                $set('tuan', $date->weekOfYear);
                                                $set('thang', $date->month);
                                                $set('nam', $date->year);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('dia_diem')
                                        ->label('Địa điểm')
                                        ->required()
                                        ->maxLength(255),
                                ]),

                                // --- Hàng 2: Giờ bắt đầu & Giờ kết thúc ---
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('gio_bat_dau')
                                        ->label('Giờ bắt đầu (24h, HH:MM)')
                                        ->required()
                                        ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/') // Regex cho định dạng HH:MM
                                        ->placeholder('Ví dụ: 15:30'),
                                    Forms\Components\TextInput::make('gio_ket_thuc')
                                        ->label('Giờ kết thúc (24h, HH:MM)')
                                        ->required()
                                        ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/')
                                        ->placeholder('Ví dụ: 17:30'),
                                ]),

                                // --- Hàng 3: Tuần, Tháng, Năm (readOnly để vẫn gửi dữ liệu) ---
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('tuan')
                                        ->label('Tuần')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(52)
                                        ->required()
                                        ->readOnly(), // readOnly thay vì disabled để vẫn gửi dữ liệu
                                    Forms\Components\TextInput::make('thang')
                                        ->label('Tháng')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(12)
                                        ->required()
                                        ->readOnly(),
                                    Forms\Components\TextInput::make('nam')
                                        ->label('Năm')
                                        ->numeric()
                                        ->minValue(2020)
                                        ->maxValue(2030)
                                        ->required()
                                        ->readOnly(),
                                ]),

                                // --- Hàng 4: Chuyên đề & Giảng viên ---
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('chuyen_de_id')
                                        ->label('Chuyên đề')
                                        ->options(function (callable $get) {
                                            $chuongTrinhId = $get('../../chuong_trinh_id');
                                            if ($chuongTrinhId) {
                                                $chuyenDeIds = DB::table('chuong_trinh_chuyen_de')
                                                    ->where('chuong_trinh_id', $chuongTrinhId)
                                                    ->pluck('chuyen_de_id');
                                                return ChuyenDe::whereIn('id', $chuyenDeIds)
                                                    ->pluck('ten_chuyen_de', 'id');
                                            }
                                            return [];
                                        })
                                        ->required()
                                        ->reactive()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('giang_vien_id')
                                        ->label('Giảng viên')
                                        ->options(function (callable $get) {
                                            $chuyenDeId = $get('chuyen_de_id');
                                            if ($chuyenDeId) {
                                                $giangVienIds = DB::table('chuyen_de_giang_vien')
                                                    ->where('chuyen_de_id', $chuyenDeId)
                                                    ->pluck('giang_vien_id');
                                                return GiangVien::whereIn('id', $giangVienIds)
                                                    ->where('tinh_trang', 'Đang giảng dạy')
                                                    ->pluck('ho_ten', 'id');
                                            }
                                            return [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive(),
                                ]),

                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(50)
                            ->columnSpanFull(),
                    ]),
                // --- HẾT PHẦN LỊCH HỌC ---
            ]);
    }

    /**
     * Validate & chuẩn hoá trước khi tạo:
     * - Chương trình phải ở trạng thái 'Đang áp dụng'
     * - Mã khóa nếu cần sẽ được tạo tự động theo QuyTacMaKhoa
     * - Mỗi buổi học: chuyên đề phải thuộc chương trình đã chọn
     * - Kiểm tra trùng giờ cho giảng viên (không cho tạo nếu giảng viên trùng)
     * - Kiểm tra định dạng giờ bắt đầu/kết thúc
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // --- LUÔN TỰ ĐỘNG TẠO `ma_khoa_hoc` và override giá trị từ form ---
        $chuongTrinhId = $data['chuong_trinh_id'] ?? null;
        if (!$chuongTrinhId) {
            throw ValidationException::withMessages(['chuong_trinh_id' => 'Phải chọn Chương trình.']);
        }

        $ct = ChuongTrinh::find($chuongTrinhId);
        if (!$ct || ($ct->tinh_trang ?? '') !== 'Đang áp dụng') {
            throw ValidationException::withMessages(['chuong_trinh_id' => 'Chương trình phải ở trạng thái "Đang áp dụng".']);
        }

        // Luôn tạo mã khóa học
        $maKhoaHoc = null;
        if (class_exists(QuyTacMaKhoa::class)) {
            try {
                // Giả định `loai_hinh_dao_tao` là một cột trong bảng `chuong_trinhs`
                $loaiHinhDaoTao = $ct->loai_hinh_dao_tao ?? null;
                if ($loaiHinhDaoTao) {
                    $maKhoaHoc = QuyTacMaKhoa::taoMaKhoaHoc($loaiHinhDaoTao);
                } else {
                    // Log hoặc xử lý nếu không có loại hình đào tạo
                    \Log::warning("Chương trình ID {$chuongTrinhId} không có trường 'loai_hinh_dao_tao'.");
                    // Có thể ném lỗi hoặc tạo mã mặc định
                    // throw new \Exception("Không xác định được loại hình đào tạo cho chương trình.");
                }
            } catch (\Throwable $e) {
                // Log lỗi nếu cần
                \Log::error('Lỗi tạo mã khóa học trong KhoaHocResource (QuyTacMaKhoa): ' . $e->getMessage());
                // Không ném lỗi nữa, để tiếp tục tạo mã mặc định bên dưới
            }
        }

        // Nếu không tạo được từ QuyTacMaKhoa, tạo mã mặc định
        if (empty($maKhoaHoc)) {
            do {
                $maKhoaHoc = 'KH-' . strtoupper(Str::random(6));
                // Kiểm tra trùng lặp để đảm bảo duy nhất
            } while (KhoaHoc::where('ma_khoa_hoc', $maKhoaHoc)->exists());
        }

        // GÁN lại giá trị cho mảng dữ liệu - Đây là bước quan trọng
        $data['ma_khoa_hoc'] = $maKhoaHoc;
        // --- HẾT TẠO `ma_khoa_hoc` ---

        // --- Validate lichHocs ---
        $lichs = $data['lichHocs'] ?? [];
        foreach ($lichs as $i => $l) {
            // Kiểm tra chuyên đề phải thuộc chương trình
            $cdId = $l['chuyen_de_id'] ?? null;
            if (!$cdId) {
                throw ValidationException::withMessages(["lichHocs.$i.chuyen_de_id" => 'Chọn chuyên đề.']);
            }
            $belongs = DB::table('chuong_trinh_chuyen_de')
                ->where('chuong_trinh_id', $chuongTrinhId)
                ->where('chuyen_de_id', $cdId)
                ->exists();
            if (!$belongs) {
                throw ValidationException::withMessages(["lichHocs.$i.chuyen_de_id" => 'Chuyên đề này không thuộc Chương trình đã chọn.']);
            }

            // --- Kiểm tra định dạng giờ ---
            $gioBatDau = $l['gio_bat_dau'] ?? null;
            $gioKetThuc = $l['gio_ket_thuc'] ?? null;

            if ($gioBatDau && !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $gioBatDau)) {
                throw ValidationException::withMessages(["lichHocs.$i.gio_bat_dau" => 'Giờ bắt đầu không đúng định dạng HH:MM (24 giờ). Ví dụ: 15:30']);
            }

            if ($gioKetThuc && !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $gioKetThuc)) {
                throw ValidationException::withMessages(["lichHocs.$i.gio_ket_thuc" => 'Giờ kết thúc không đúng định dạng HH:MM (24 giờ). Ví dụ: 17:30']);
            }

            // Kiểm tra giờ kết thúc phải sau giờ bắt đầu
            if ($gioBatDau && $gioKetThuc) {
                 // Chuyển HH:MM thành số phút để so sánh
                 list($h_bd, $m_bd) = array_pad(explode(':', $gioBatDau), 2, '00');
                 list($h_kt, $m_kt) = array_pad(explode(':', $gioKetThuc), 2, '00');
                 $phutBatDau = (int)$h_bd * 60 + (int)$m_bd;
                 $phutKetThuc = (int)$h_kt * 60 + (int)$m_kt;

                 if ($phutKetThuc <= $phutBatDau) {
                     throw ValidationException::withMessages(["lichHocs.$i.gio_ket_thuc" => 'Giờ kết thúc phải sau giờ bắt đầu.']);
                 }
            }

            // Kiểm tra trùng giờ giảng viên (tùy chọn)
            $gvId = $l['giang_vien_id'] ?? null;
            $ngayHoc = $l['ngay_hoc'] ?? null;

            if ($gvId && $ngayHoc && $gioBatDau && $gioKetThuc) {
                $overlap = LichHoc::where('giang_vien_id', $gvId)
                    ->where('ngay_hoc', $ngayHoc)
                    ->where(function ($q) use ($gioBatDau, $gioKetThuc) {
                        // Logic kiểm tra trùng giờ
                        $q->where('gio_bat_dau', '<', $gioKetThuc)
                          ->where('gio_ket_thuc', '>', $gioBatDau);
                    })
                    // Loại trừ chính bản ghi đang kiểm tra (cho trường hợp edit) - không cần vì đây là tạo mới
                    // ->where('id', '!=', $l['id'] ?? null)
                    ->exists();

                if ($overlap) {
                    throw ValidationException::withMessages(["lichHocs.$i.giang_vien_id" => 'Giảng viên bị trùng giờ vào ngày đã chọn.']);
                }
            }
        }
        // --- HẾT Validate lichHocs ---

        // --- (Tùy chọn) Xử lý gửi email nếu được yêu cầu ---
        // Lưu ý: Việc gửi email ở đây là khi TẠO MỚI khóa học.
        if (!empty($data['gui_email'])) {
            try {
                // a. Lấy thông tin cần thiết
                $chuongTrinh = ChuongTrinh::find($chuongTrinhId);
                $tenChuongTrinh = $chuongTrinh->ten_chuong_trinh ?? 'N/A';

                // b. Tìm template và account mặc định
                // Giả định bạn có một template với loai_thong_bao = 'tao_khoa_hoc'
                $template = EmailTemplate::where('loai_thong_bao', 'tao_khoa_hoc')->first();
                $emailAccount = EmailAccount::where('is_default', 1)->where('active', 1)->first();

                if ($template && $emailAccount) {
                    // c. Chuẩn bị nội dung email
                    $tieuDe = str_replace(
                        ['{ma_khoa_hoc}', '{ten_chuong_trinh}'],
                        [$data['ma_khoa_hoc'], $tenChuongTrinh],
                        $template->tieu_de
                    );
                    $noiDung = str_replace(
                        ['{ma_khoa_hoc}', '{ten_chuong_trinh}'],
                        [$data['ma_khoa_hoc'], $tenChuongTrinh],
                        $template->noi_dung
                    );

                    // --- d. Gửi email (ví dụ dùng Mail facade) ---
                    // Bạn cần cấu hình mail trong .env
                    // Giả sử gửi đến admin, bạn có thể thay bằng danh sách người nhận thực tế
                    $recipientEmail = 'admin@example.com'; // <<< Thay bằng email thật

                    Mail::raw($noiDung, function ($message) use ($tieuDe, $emailAccount, $recipientEmail) {
                        $message->to($recipientEmail)
                                ->subject($tieuDe)
                                ->from($emailAccount->email, $emailAccount->name);
                    });

                    // e. Lưu log email
                    EmailLog::create([
                        'recipient_email' => $recipientEmail,
                        'subject' => $tieuDe,
                        'content' => $noiDung,
                        'status' => 'sent',
                        'email_account_id' => $emailAccount->id,
                    ]);

                    // f. (Tùy chọn) Thông báo cho người dùng trên giao diện
                    // Notification::make()
                    //     ->title('Email thông báo đã được gửi')
                    //     ->success()
                    //     ->send();
                } else {
                    \Log::warning("Không tìm thấy template 'tao_khoa_hoc' hoặc tài khoản email mặc định khi tạo khóa học {$data['ma_khoa_hoc']}.");
                }
            } catch (\Exception $e) {
                \Log::error("Lỗi khi xử lý gửi email tạo khóa học {$data['ma_khoa_hoc']}: " . $e->getMessage());
                // Có thể bỏ qua lỗi gửi email, vẫn cho phép tạo khóa học
                // Hoặc throw $e; nếu muốn dừng tạo khóa học khi gửi email thất bại
            }
            // Dọn dẹp key tạm thời không cần lưu vào DB
            unset($data['gui_email']);
        }
        // --- HẾT Xử lý gửi email ---

        return $data;
    }

    // Giữ nguyên hoặc cập nhật mutateFormDataBeforeSave nếu cần logic riêng cho edit
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Nếu bạn chỉ cần tạo mã khi tạo mới, có thể bỏ qua phần này hoặc chỉ gọi nếu $data['ma_khoa_hoc'] trống
        // Ở đây mình kiểm tra nếu là tạo mới thì gọi mutateFormDataBeforeCreate
        if (empty($data['id'])) { // Kiểm tra nếu không có 'id' thì là tạo mới
             return static::mutateFormDataBeforeCreate($data);
        }
        // Nếu là edit, bạn có thể thêm logic validate khác ở đây nếu cần
        // Ví dụ: Không cho phép thay đổi chương trình sau khi tạo
        // if (isset($data['chuong_trinh_id'])) {
        //     unset($data['chuong_trinh_id']); // Bỏ qua thay đổi chương trình
        // }
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // --- Bắt đầu: Sửa cột hiển thị tên khóa học ---
                Tables\Columns\TextColumn::make('ten_va_ma')
                    ->label('Tên Khóa học')
                    ->getStateUsing(fn (KhoaHoc $record) => ($record->chuongTrinh?->ten_chuong_trinh ?? 'N/A') . ', ' . $record->ma_khoa_hoc)
                    ->searchable(['chuongTrinh.ten_chuong_trinh', 'ma_khoa_hoc']) // Tìm kiếm theo tên chương trình và mã
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->join('chuong_trinhs', 'khoa_hocs.chuong_trinh_id', '=', 'chuong_trinhs.id')
                            ->orderBy('chuong_trinhs.ten_chuong_trinh', $direction)
                            ->orderBy('khoa_hocs.ma_khoa_hoc', $direction);
                    }),
                // --- Kết thúc: Sửa cột hiển thị tên khóa học ---

                // Loại bỏ hoặc comment dòng dưới vì không có cột `ten_khoa_hoc` trong DB
                // Tables\Columns\TextColumn::make('ten_khoa_hoc')->label('Tên khóa học')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('ma_khoa_hoc')->label('Mã khóa học')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('chuongTrinh.ten_chuong_trinh')->label('Chương trình')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nam')->label('Năm')->sortable()->alignCenter(),

                // Thay thế BadgeColumn nếu không dùng Enum
                Tables\Columns\TextColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->searchable()
                    ->sortable(),

                // --- Cột "Số buổi" đã SỬA ĐÚNG ---
                Tables\Columns\TextColumn::make('lichHocs_count')
                    ->label('Số buổi')
                    ->counts('lichHocs') // Tên quan hệ trong model KhoaHoc PHẢI là 'lichHocs'
                    ->alignCenter(),
                // --- Hết cột "Số buổi" ---

                Tables\Columns\TextColumn::make('created_at')->label('Tạo lúc')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trang_thai')
                    ->label('Trạng thái')
                    ->options([
                        'Soạn thảo' => 'Soạn thảo',
                        'Kế hoạch' => 'Kế hoạch',
                        'Ban hành' => 'Ban hành',
                        'Đang đào tạo' => 'Đang đào tạo',
                        'Kết thúc' => 'Kết thúc',
                    ]),

                Tables\Filters\SelectFilter::make('nam')->label('Năm')->options(fn () => array_combine(range(2020, 2030), range(2020, 2030))),
                Tables\Filters\SelectFilter::make('chuong_trinh_id')
                    ->label('Chương trình')
                    ->relationship('chuongTrinh', 'ten_chuong_trinh')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Thêm ViewAction
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Cẩn thận với xóa cascade
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tạo Khóa học mới')->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Nếu bạn có RelationManagers, thêm vào đây
    public static function getRelations(): array
    {
        return [
            // Ví dụ:
            // \App\Filament\Resources\KhoaHocResource\RelationManagers\LichHocsRelationManager::class,
            // \App\Filament\Resources\KhoaHocResource\RelationManagers\DangKiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'view' => Pages\ViewKhoaHoc::route('/{record}'), // Thêm ViewPage - Đảm bảo trang này tồn tại
            'edit' => Pages\EditKhoaHoc::route('/{record}/edit'),
        ];
    }
}
