<?php

namespace App\Filament\Resources\SocialLinks;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\SocialLinks\Pages\CreateSocialLink;
use App\Filament\Resources\SocialLinks\Pages\EditSocialLink;
use App\Filament\Resources\SocialLinks\Pages\ListSocialLinks;
use App\Models\Image;
use App\Models\SocialLink;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Select as SchemaSelect;
use Filament\Schemas\Components\TextInput as SchemaTextInput;
use Filament\Schemas\Components\Toggle as SchemaToggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $recordTitleAttribute = 'platform';

    protected static UnitEnum|string|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Liên kết mạng xã hội';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Liên kết mạng xã hội';

    protected static ?string $pluralModelLabel = 'Các liên kết mạng xã hội';

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::query()
            ->where('active', true)
            ->count();

        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaGrid::make()
                    ->schema([
                        SchemaTextInput::make('platform')
                            ->label('Tên mạng xã hội')
                            ->required()
                            ->maxLength(255),
                        SchemaTextInput::make('url')
                            ->label('Đường dẫn')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        SchemaSelect::make('icon_image_id')
                            ->label('Biểu tượng')
                            ->relationship('iconImage', 'file_path')
                            ->searchable()
                            ->preload(),
                        SchemaToggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('iconImage'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Tên mạng xã hội')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\ImageColumn::make('iconImage.file_path')
                    ->label('Biểu tượng')
                    ->disk('public')
                    ->width(40)
                    ->height(40)
                    ->circular(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Hiển thị'),
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
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialLinks::route('/'),
            'create' => CreateSocialLink::route('/create'),
            'edit' => EditSocialLink::route('/{record}/edit'),
        ];
    }
}
