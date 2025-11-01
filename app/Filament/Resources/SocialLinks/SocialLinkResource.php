<?php

namespace App\Filament\Resources\SocialLinks;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\SocialLinks\Pages\CreateSocialLink;
use App\Filament\Resources\SocialLinks\Pages\EditSocialLink;
use App\Filament\Resources\SocialLinks\Pages\ListSocialLinks;
use App\Filament\Resources\SocialLinks\Pages\ViewSocialLink;
use App\Models\Image;
use App\Models\SocialLink;
use Filament\Resources\Resource;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Select as SchemaSelect;
use Filament\Schemas\Components\TextInput as SchemaTextInput;
use Filament\Schemas\Components\Toggle as SchemaToggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Liên kết mạng xã hội';

    protected static ?string $pluralModelLabel = 'Các liên kết mạng xã hội';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaGrid::make()
                    ->schema([
                        SchemaTextInput::make('platform')
                            ->label('Nền tảng')
                            ->required()
                            ->maxLength(255),
                        SchemaTextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        SchemaSelect::make('icon_image_id')
                            ->label('Hình ảnh biểu tượng')
                            ->options(Image::active()->pluck('file_path', 'id'))
                            ->searchable(),
                        SchemaTextInput::make('order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->default(0),
                        SchemaToggle::make('active')
                            ->label('Hoạt động')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
        Tables\Columns\TextColumn::make('platform')
        ->label('Nền tảng')
        ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('url')
            ->label('URL')
        ->searchable(),
        Tables\Columns\ImageColumn::make('iconImage.file_path')
        ->label('Biểu tượng')
            ->disk('public')
        ->circular(),
        Tables\Columns\TextColumn::make('order')
        ->label('Thứ tự')
                ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Hoạt động')
                    ->boolean(),
            ])
            ->filters([
            //
            ])
            ->defaultSort('order');
    }

    public static function getTableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    public static function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
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
            'view' => ViewSocialLink::route('/{record}'),
            'edit' => EditSocialLink::route('/{record}/edit'),
        ];
    }
}
