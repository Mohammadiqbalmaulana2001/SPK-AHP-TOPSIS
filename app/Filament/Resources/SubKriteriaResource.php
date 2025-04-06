<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubKriteriaResource\Pages;
use App\Models\Kategori;
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
    
    protected static ?string $navigationGroup = 'Master Data';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kriteria_id')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('kategori_id', null)),
                Forms\Components\Select::make('kategori_id')
                    ->label('Kategori')
                    ->options(function (callable $get) {
                        $kriteriaId = $get('kriteria_id');
                        if (!$kriteriaId) {
                            return [];
                        }
                        return Kategori::where('kriteria_id', $kriteriaId)->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('kode')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bobot')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01),
                Forms\Components\TextInput::make('nilai')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kriteria.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bobot')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai')
                    ->numeric()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('kriteria_id')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->options(Kategori::all()->pluck('nama', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSubKriterias::route('/'),
            'create' => Pages\CreateSubKriteria::route('/create'),
            // 'view' => Pages\ViewSubKriteria::route('/{record}'),
            'edit' => Pages\EditSubKriteria::route('/{record}/edit'),
        ];
    }
}