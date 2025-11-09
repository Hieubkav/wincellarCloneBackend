<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ImageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Preview')
                    ->schema([
                        ImageEntry::make('url')
                            ->label('Image')
                            ->square()
                            ->hidden(fn ($record) => blank($record->url)),
                    ]),
                Section::make('Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('file_path')
                                    ->label('File path')
                                    ->copyable()
                                    ->columnSpan(2),
                                TextEntry::make('disk')
                                    ->label('Disk')
                                    ->badge(),
                                TextEntry::make('order')
                                    ->label('Order')
                                    ->numeric(),
                                IconEntry::make('active')
                                    ->label('Active')
                                    ->boolean(),
                                TextEntry::make('width')
                                    ->label('Width (px)')
                                    ->numeric(),
                                TextEntry::make('height')
                                    ->label('Height (px)')
                                    ->numeric(),
                                TextEntry::make('mime')
                                    ->label('MIME type'),
                            ]),
                        TextEntry::make('alt')
                            ->label('Alt text')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Relationships / Quan hệ')
                    ->description('Ảnh này đang được sử dụng bởi:')
                    ->icon('heroicon-o-link')
                    ->iconColor(fn (Image $record) => $record->model_type && $record->model_id ? 'warning' : 'gray')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('model_type')
                                    ->label('Loại')
                                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'Không có')
                                    ->badge()
                                    ->color(fn (?string $state): string => match($state) {
                                        'App\Models\Product' => 'success',
                                        'App\Models\Article' => 'info',
                                        default => 'gray',
                                    })
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('model_id')
                                    ->label('ID')
                                    ->placeholder('—')
                                    ->numeric()
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('model.name')
                                    ->label('Tên')
                                    ->placeholder('Không có owner')
                                    ->columnSpan(2)
                                    ->url(fn (Image $record) => self::getOwnerUrl($record), shouldOpenInNewTab: true)
                                    ->color('primary')
                                    ->weight(FontWeight::SemiBold),
                            ]),
                        Actions::make([
                            Action::make('goToOwner')
                                ->label('Đi đến Owner')
                                ->icon('heroicon-o-arrow-top-right-on-square')
                                ->color('primary')
                                ->visible(fn (Image $record) => $record->model_type && $record->model_id)
                                ->url(fn (Image $record) => self::getOwnerUrl($record), shouldOpenInNewTab: true),
                            Action::make('removeFromOwner')
                                ->label('Xóa khỏi Owner')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Xóa ảnh khỏi Owner')
                                ->modalDescription('Xóa ảnh này khỏi owner. File ảnh gốc vẫn còn trong storage.')
                                ->modalSubmitActionLabel('Xóa')
                                ->visible(fn (Image $record) => $record->model_type && $record->model_id)
                                ->action(function (Image $record) {
                                    $ownerType = class_basename($record->model_type);
                                    $ownerId = $record->model_id;
                                    
                                    // Soft delete the image record
                                    $record->delete();
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('Đã xóa')
                                        ->body("Đã xóa ảnh khỏi {$ownerType} #{$ownerId}.")
                                        ->send();
                                }),
                        ])
                        ->fullWidth(),
                    ]),
                Section::make('Extra attributes')
                    ->collapsed()
                    ->schema([
                        KeyValueEntry::make('extra_attributes')
                            ->label('Attributes')
                            ->hidden(fn ($record) => blank($record->extra_attributes ?? []))
                            ->columnSpanFull(),
                    ]),
                Section::make('Timestamps')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created at')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Last updated')
                                    ->dateTime(),
                                TextEntry::make('deleted_at')
                                    ->label('Deleted at')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
    
    protected static function getOwnerUrl(Image $record): ?string
    {
        if (!$record->model_type || !$record->model_id) {
            return null;
        }
        
        return match($record->model_type) {
            Product::class => route('filament.admin.resources.products.edit', ['record' => $record->model_id]),
            Article::class => route('filament.admin.resources.articles.edit', ['record' => $record->model_id]),
            default => null,
        };
    }
}
