<?php

namespace App\Filament\Resources\Articles\ArticleResource\RelationManagers;

use App\Filament\Traits\ManagesImageUploads;
use App\Models\Image;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    use ManagesImageUploads;

    protected static string $relationship = 'images';

    protected static ?string $title = 'Hình ảnh bài viết';

    protected static ?string $recordTitleAttribute = 'alt';

    protected function getUploadDirectory(): string
    {
        return 'articles';
    }

    protected function getFilenamePrefix(): string
    {
        return 'article';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema($this->getImageUploadFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getImageTableColumns())
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->headerActions([
                CreateAction::make()
                    ->label('Tải lên mới')
                    ->icon('heroicon-o-arrow-up-tray'),
Action::make('selectFromLibrary')
                    ->label('Chọn từ thư viện')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->modalHeading('Chọn ảnh từ thư viện')
                    ->modalDescription('Chọn ảnh có sẵn trong hệ thống để thêm vào bài viết')
                    ->modalSubmitActionLabel('Thêm ảnh đã chọn')
                    ->modalWidth('7xl')
                    ->form(fn (RelationManager $livewire) => $this->buildLibrarySelectionForm($livewire))
                    ->action(fn (array $data, RelationManager $livewire) => $this->handleLibrarySelection($data, $livewire)),
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
