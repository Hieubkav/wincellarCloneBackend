<?php

namespace App\Filament\Resources\Articles;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Models\Article;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';

    protected static UnitEnum|string|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Bài viết';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Bài viết';

    protected static ?string $pluralModelLabel = 'Các bài viết';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->helperText('Tiêu đề bài viết, nên rõ ràng và hấp dẫn')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->helperText('Đường dẫn hiển thị trên website. Ví dụ: cach-chon-ruou-vang-ngon')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash']),
                        Select::make('author_id')
                            ->label('Tác giả')
                            ->helperText('Người viết bài')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('excerpt')
                            ->label('Tóm tắt')
                            ->helperText('Đoạn giới thiệu ngắn, hiển thị ở danh sách bài viết')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->helperText('Bật để xuất bản bài viết')
                            ->default(true),
                    ])
                    ->columns(2),
                RichEditor::make('content')
                    ->label('Nội dung')
                    ->helperText('Nội dung chi tiết của bài viết')
                    ->required()
                    ->columnSpanFull(),
                Grid::make()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Tiêu đề SEO')
                            ->helperText('Tiêu đề hiển thị trên Google (tối đa 60 ký tự)')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Mô tả SEO')
                            ->helperText('Mô tả ngắn cho Google (tối đa 160 ký tự)')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public static function getTableActions(): array
    {
        return [
            ViewAction::make()->iconButton(),
            EditAction::make()->iconButton(),
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
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view' => ViewArticle::route('/{record}'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
