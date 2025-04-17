<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubKriteriaResource\Pages;
use App\Models\Kriteria;
use App\Models\SubKriteria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubKriteriaResource extends Resource
{
    protected static ?string $model = SubKriteria::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    
    protected static ?string $navigationLabel = 'Sub Kriteria';
    
    protected static ?string $navigationGroup = 'Data Master';
    
    protected static ?int $navigationSort = 4;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kriteria_id')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('kode')
                    ->label('Kode')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'benefit' => 'Benefit',
                        'cost' => 'Cost',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kriteria.nama')
                    ->label('Kriteria')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'benefit' => 'success',
                        'cost' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                Tables\Columns\TextColumn::make('bobot_global')
                    ->label('Bobot Global')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kriteria_id')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'benefit' => 'Benefit',
                        'cost' => 'Cost',
                    ]),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
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
            'index' => Pages\ListSubKriterias::route('/'),
            // 'create' => Pages\CreateSubKriteria::route('/create'),
            // 'view' => Pages\ViewSubKriteria::route('/{record}'),
            // 'edit' => Pages\EditSubKriteria::route('/{record}/edit'),
        ];
    }
}