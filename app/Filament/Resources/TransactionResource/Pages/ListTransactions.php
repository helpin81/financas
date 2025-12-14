<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\Import\ImportTransactionService;
use App\Models\Account;
use App\Models\ImportModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Importar Extrato')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Select::make('account_id')
                        ->label('Conta / Cartão')
                        ->options(Account::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Select::make('import_model_id')
                        ->label('Modelo de Importação (PDF)')
                        ->options(ImportModel::all()->pluck('institution_name', 'id'))
                        ->placeholder('Selecione para PDF')
                        ->helperText('Obrigatório para arquivos PDF'),
                    FileUpload::make('file')
                        ->label('Arquivo (OFX ou PDF)')
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                     // Resolve service manually (or via injection if possible in Action, but easier here)
                     $service = app(ImportTransactionService::class);
                     
                     $account = Account::find($data['account_id']);
                     $model = isset($data['import_model_id']) ? ImportModel::find($data['import_model_id']) : null;
                     
                     // FileUpload returns path relative to disk. We need absolute path or stream.
                     // Since we used 'local' disk, it's in storage/app/imports/...
                     $filePath = storage_path('app/' . $data['file']);
                     
                     // Mocking an UploadedFile isn't easy here, but our service expects it?
                     // Let's refactor service to accept path if needed, OR create UploadedFile.
                     // Actually better: Modify Service to accept path + extension.
                     
                     // Wait, Service expects UploadedFile. Let's fix Service to be more flexible first?
                     // Or just wrap it here.
                     
                     // For now, let's assume service needs update. I'll update Service first.
                     // But strictly, let's pass a dummy UploadedFile or update service.
                     // Updating service is cleaner.
                     
                     try {
                         $count = $service->importFileFromPath($filePath, $account, $model);
                         
                         Notification::make()
                            ->title('Importação Concluída')
                            ->body("$count transações importadas com sucesso.")
                            ->success()
                            ->send();
                     } catch (\Exception $e) {
                         Notification::make()
                            ->title('Erro na Importação')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                     }
                }),
        ];
    }
}
