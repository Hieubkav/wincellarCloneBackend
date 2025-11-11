<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Cấu hình chung';

    /**
     * Get the record to edit (first Setting record, or create if not exists)
     */
    public function mount(int|string $record = null): void
    {
        $this->record = Setting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => config('app.name'),
                'meta_default_title' => config('app.name'),
            ]
        );

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Đã lưu cấu hình thành công';
    }
}
