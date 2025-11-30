<?php

namespace App\Filament\Resources\ProductTypes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Danh mục';

    protected static ?string $modelLabel = 'danh mục';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Tên danh mục')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state, '-'))),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->default(fn () => (int) ($this->ownerRecord->categories()->max('order') ?? 0) + 1),

                Toggle::make('active')
                    ->label('Hiển thị')
                    ->default(true),

                Hidden::make('type_id')
                    ->default(fn () => $this->ownerRecord->getKey())
                    ->dehydrated(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('Số SP')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Hiện')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm danh mục')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type_id'] = $this->ownerRecord->getKey();
                        $data['slug'] = $data['slug'] ?: Str::slug((string) ($data['name'] ?? ''), '-');
                        $data['order'] = $data['order'] ?? (int) ($this->ownerRecord->categories()->max('order') ?? 0) + 1;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type_id'] = $this->ownerRecord->getKey();
                        $data['slug'] = $data['slug'] ?: Str::slug((string) ($data['name'] ?? ''), '-');
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->paginated([10, 25, 50, 100, 'all']);
    }
}

