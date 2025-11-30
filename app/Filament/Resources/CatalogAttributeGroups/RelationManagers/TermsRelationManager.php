<?php

namespace App\Filament\Resources\CatalogAttributeGroups\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class TermsRelationManager extends RelationManager
{
    protected static string $relationship = 'terms';

    protected static ?string $title = 'Giá trị';

    protected static ?string $modelLabel = 'giá trị';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Ẩn relation manager với nhóm nhập tay (nhap_tay).
        if (($ownerRecord->filter_type ?? null) === 'nhap_tay') {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Tên')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state, '-'))),

                Hidden::make('slug')
                    ->dehydrated(true)
                    ->default(fn (callable $get) => Str::slug((string) ($get('name') ?? ''), '-'))
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                        // Giới hạn unique theo group hiện tại.
                        return $rule->where('group_id', $this->ownerRecord->getKey());
                    }),

                Hidden::make('group_id')
                    ->default(fn () => $this->ownerRecord->getKey())
                    ->dehydrated(true),

                Hidden::make('position')
                    ->default(fn () => (int) ($this->ownerRecord->terms()->max('position') ?? 0) + 1)
                    ->dehydrated(true),

                TextInput::make('description')
                    ->label('Mô tả')
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('icon_value')
                    ->label('Icon (tuỳ chọn)')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Hiển thị')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Giá trị thuộc tính')
            ->recordTitleAttribute('name')
            ->defaultSort('position')
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
                IconColumn::make('is_active')
                    ->label('Hiện')
                    ->boolean(),
            ])
            ->reorderable('position')
            ->defaultPaginationPageOption(25)
            ->recordUrl(null)
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm giá trị')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['group_id'] = $this->ownerRecord->getKey();
                        $data['slug'] = $data['slug'] ?: Str::slug((string) ($data['name'] ?? ''), '-');
                        $data['position'] = $data['position'] ?? (int) ($this->ownerRecord->terms()->max('position') ?? 0) + 1;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['group_id'] = $this->ownerRecord->getKey();
                        $data['slug'] = $data['slug'] ?: Str::slug((string) ($data['name'] ?? ''), '-');
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->paginated([10, 25, 50, 100, 'all']);
    }
}
