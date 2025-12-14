<?php

namespace App\Filament\Resources\ImportModelResource\Pages;

use App\Filament\Resources\ImportModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImportModels extends ListRecords
{
    protected static string $resource = ImportModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
