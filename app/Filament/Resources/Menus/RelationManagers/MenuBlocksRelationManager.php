<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Filament\Resources\MenuBlocks\MenuBlockResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuBlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Các cột mega menu';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Tiêu đề cột')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('VD: Theo loại, Theo quốc gia')
                    ->helperText('Tiêu đề hiển thị ở đầu cột trong mega menu'),
                Toggle::make('active')
                    ->label('Hiển thị')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(60)
                    ->alignCenter()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Tiêu đề cột')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('info'),
                TextColumn::make('items_count')
                    ->label('Số items')
                    ->counts('items')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm cột')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->modalHeading('Thêm cột mega menu'),
            ])
            ->recordActions([
                Action::make('editItems')
                    ->label('Sửa items')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary')
                    ->modalHeading(fn ($record) => "Sửa items: {$record->title}")
                    ->modalSubmitActionLabel('Lưu')
                    ->modalCancelActionLabel('Hủy')
                    ->fillForm(fn ($record) => [
                        'items' => $record->items->map(fn ($item) => [
                            'id' => $item->id,
                            'label' => $item->label,
                            'href' => $item->href,
                        ])->toArray(),
                    ])
                    ->form([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Tiêu đề')
                                    ->required(),
                                TextInput::make('href')
                                    ->label('URL'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('Thêm item'),
                    ])
                    ->action(function ($record, array $data) {
                        $existingIds = collect($data['items'])->pluck('id')->filter()->toArray();
                        $record->items()->whereNotIn('id', $existingIds)->delete();

                        foreach ($data['items'] as $index => $itemData) {
                            if (isset($itemData['id'])) {
                                $record->items()->where('id', $itemData['id'])->update([
                                    'label' => $itemData['label'],
                                    'href' => $itemData['href'],
                                    'order' => $index,
                                ]);
                            } else {
                                $record->items()->create([
                                    'label' => $itemData['label'],
                                    'href' => $itemData['href'],
                                    'order' => $index,
                                ]);
                            }
                        }
                    }),
                Action::make('open')
                    ->label('Mở')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconButton()
                    ->color('info')
                    ->url(fn ($record) => MenuBlockResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                EditAction::make()->iconButton()->color('warning'),
                DeleteAction::make()->iconButton()->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Chưa có cột nào')
            ->emptyStateDescription('Thêm cột để xây dựng mega menu (VD: Theo loại, Theo quốc gia)')
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }
}
