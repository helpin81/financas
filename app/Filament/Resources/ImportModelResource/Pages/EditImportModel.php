<?php

namespace App\Filament\Resources\ImportModelResource\Pages;

use App\Filament\Resources\ImportModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImportModel extends EditRecord
{
    protected static string $resource = ImportModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
