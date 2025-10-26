<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;            // Filament 3: dùng Forms\Form
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;          // Filament 3: dùng Tables\Table
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder; // ✅ để type-hint cho modifyQueryUsing

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Thông báo & Tuyển sinh';
    protected static ?string $pluralModelLabel = 'Thông báo & Tuyển sinh';
    protected static ?string $modelLabel = 'thông báo';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        // Ưu tiên Tiptap nếu có; fallback RichEditor
        $contentEditor =
            class_exists(\Awcodes\TiptapEditor\TiptapEditor::class)
                ? \Awcodes\TiptapEditor\TiptapEditor::make('content')
                    ->label('Nội dung')
                    ->profile('default')
                    ->tools([
                        'heading','bold','italic','strike','underline',
                        'bulletList','orderedList','blockquote','codeBlock',
                        'alignLeft','alignCenter','alignRight','alignJustify',
                        'color','highlight','hurdle','hr','table','details',
                        'link','image','video','code','subscript','superscript',
                        'undo','redo','clear',
                    ])
                    ->columnSpanFull()
                : Forms\Components\RichEditor::make('content')
                    ->label('Nội dung')
                    ->disableToolbarButtons(['attachFiles'])
                    ->columnSpanFull();

        return $form->schema([
            Section::make('Thông tin chính')->schema([
                TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->unique(ignoreRecord: true)
                    ->helperText('Để trống sẽ tự tạo theo tiêu đề.'),
                DateTimePicker::make('publish_at')->label('Giờ phát hành'),
                DateTimePicker::make('expires_at')->label('Ngày hết hiệu lực'),
                Forms\Components\Select::make('status')->label('Trạng thái')
                    ->options([
                        'draft'   => 'Dự thảo',
                        'active'  => 'Đang áp dụng',
                        'expired' => 'Hết hiệu lực',
                    ])->required()->default('draft'),
                Toggle::make('is_featured')->label('Tiêu điểm (hiển thị ở trang chủ)')->inline(false),
                Toggle::make('is_popup')->label('Cửa sổ tiêu điểm (Popup khi vào trang chủ)')->inline(false),
                Toggle::make('enable_marquee')->label('Chuyển tiếp tin ở trang chủ')->inline(false)->default(true),
                TextInput::make('scroll_speed')->label('Tốc độ chuyển (giây)')
                    ->numeric()->minValue(2)->maxValue(30)->default(6),
                TextInput::make('redirect_url')->label('Liên kết chuyển tiếp (tuỳ chọn)')
                    ->url()->suffixIcon('heroicon-o-arrow-top-right-on-square'),
            ])->columns(2),

            Section::make('Phương tiện')->schema([
                FileUpload::make('cover_path')->label('Ảnh bìa')
                    ->image()->directory('announcements')
                    ->imageEditor()->downloadable()->openable(),

                FileUpload::make('video_path')->label('Video (tải lên)')
                    ->directory('announcements')
                    ->acceptedFileTypes(['.mp4', '.webm', '.mov', '.avi', '.mkv', '.mpeg']),

                TextInput::make('video_url')->label('Hoặc URL video')->url()
                    ->placeholder('https://youtu.be/...'),
            ])->columns(3),

            Section::make('Nội dung')->schema([$contentEditor])->collapsed(false),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ✅ Sửa: dùng type-hint Builder để Container resolve được
            ->modifyQueryUsing(fn (Builder $query) => $query->ordered())
            ->columns([
                ImageColumn::make('cover_path')->label('Ảnh')->square()->height(44),
                TextColumn::make('title')->label('Tiêu đề')->searchable()->limit(60)->wrap(),
                BadgeColumn::make('status')->label('Trạng thái')->colors([
                    'gray'    => 'draft',
                    'success' => 'active',
                    'danger'  => 'expired',
                ])->formatStateUsing(function ($state, $record) {
                    return match (true) {
                        $record->is_expired      => 'Hết hiệu lực',
                        $state === 'draft'       => 'Dự thảo',
                        $state === 'active'      => 'Đang áp dụng',
                        default                  => 'Hết hiệu lực',
                    };
                }),
                TextColumn::make('publish_at')->label('Giờ phát hành')->dateTime('H:i d/m/Y')->sortable(),
                TextColumn::make('expires_at')->label('Hết hiệu lực')->dateTime('H:i d/m/Y')->sortable(),
                IconColumn::make('is_featured')->boolean()->label('Tiêu điểm'),
                IconColumn::make('is_popup')->boolean()->label('Popup'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options([
                    'draft'   => 'Dự thảo',
                    'active'  => 'Đang áp dụng',
                    'expired' => 'Hết hiệu lực',
                ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Tiêu điểm'),
                Tables\Filters\TernaryFilter::make('is_popup')->label('Popup'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('announcements.show', $record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit'   => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
