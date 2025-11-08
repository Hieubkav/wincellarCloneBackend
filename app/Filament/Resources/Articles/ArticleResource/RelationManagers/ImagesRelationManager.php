<?php

namespace App\Filament\Resources\Articles\ArticleResource\RelationManagers;

use App\Models\Image;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Hình ảnh bài viết';

    protected static ?string $recordTitleAttribute = 'alt';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                FileUpload::make('file_path')
                    ->label('Tải lên hình ảnh')
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->image()
                    ->disk('public')
                    ->directory('articles')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/*'])
                    ->saveUploadedFileUsing(function ($file) {
                        $filename = uniqid('article_') . '.webp';
                        $path = 'articles/' . $filename;

                        $manager = new ImageManager(new Driver());
                        $image = $manager->read($file->getRealPath());

                        if ($image->width() > 1200) {
                            $image->scale(width: 1200);
                        }

                        $webp = $image->toWebp(quality: 85);
                        Storage::disk('public')->put($path, $webp);

                        return $path;
                    })
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if (!$state) {
                            return;
                        }

                        try {
                            $disk = $get('disk') ?? 'public';
                            $fullPath = Storage::disk($disk)->path($state);
                            
                            if (file_exists($fullPath)) {
                                [$width, $height] = getimagesize($fullPath);
                                $set('width', $width);
                                $set('height', $height);
                                $set('mime', 'image/webp');
                            }
                        } catch (\Throwable $e) {
                            // Ignore errors
                        }
                    })
                    ->columnSpanFull(),

                Toggle::make('active')
                    ->label('Hiển thị')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Ảnh')
                    ->disk('public')
                    ->width(80)
                    ->height(80)
                    ->defaultImageUrl('/images/placeholder.png'),

                TextColumn::make('width')
                    ->label('Kích thước')
                    ->formatStateUsing(fn($record) => $record->width && $record->height ? "{$record->width}x{$record->height}" : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('active')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Hiển thị' : 'Ẩn')
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->headerActions([
                CreateAction::make()->label('Tạo'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }
}
