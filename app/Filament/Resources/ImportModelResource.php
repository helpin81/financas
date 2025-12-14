<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportModelResource\Pages;
use App\Filament\Resources\ImportModelResource\RelationManagers;
use App\Models\ImportModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImportModelResource extends Resource
{
    protected static ?string $model = ImportModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\TextInput::make('institution_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('file_type')
                    ->options([
                        'pdf' => 'PDF',
                        'ofx' => 'OFX',
                        'csv' => 'CSV',
                    ])
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'account' => 'Conta Corrente',
                        'credit_card' => 'Cartão de Crédito',
                    ])
                    ->required(),
                Forms\Components\KeyValue::make('rules')
                    ->label('Regras de Extração (JSON/Regex)')
                    ->keyLabel('Campo')
                    ->valueLabel('Regex / Padrão')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('institution_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_type')
                    ->badge()
                    ->colors([
                        'info' => 'pdf',
                        'success' => 'ofx',
                        'warning' => 'csv',
                    ]),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'account' => 'Conta',
                        'credit_card' => 'Cartão',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListImportModels::route('/'),
            'create' => Pages\CreateImportModel::route('/create'),
            'edit' => Pages\EditImportModel::route('/{record}/edit'),
        ];
    }
}
