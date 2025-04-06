<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenilaianResource\Pages;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Models\SubKriteria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class PenilaianResource extends Resource
{
    protected static ?string $model = Penilaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'Penilaian';
    
    protected static ?string $navigationGroup = 'Penilaian';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('alternatif_id')
                    ->label('Alternatif')
                    ->options(Alternatif::all()->pluck('nama', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('sub_kriteria_id')
                    ->label('Sub Kriteria')
                    ->options(function () {
                        return SubKriteria::query()
                            ->join('kriterias', 'sub_kriterias.kriteria_id', '=', 'kriterias.id')
                            ->select('sub_kriterias.id', DB::raw("CONCAT(kriterias.nama, ' - ', sub_kriterias.nama) as full_name"))
                            ->pluck('full_name', 'sub_kriterias.id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('nilai')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alternatif.nama')
                    ->label('Alternatif')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subKriteria.kriteria.nama')
                    ->label('Kriteria')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subKriteria.nama')
                    ->label('Sub Kriteria')
                    ->sortable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('alternatif_id')
                    ->label('Alternatif')
                    ->options(Alternatif::all()->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('kriteria')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $value): Builder => $query
                                    ->whereHas('subKriteria', function (Builder $query) use ($value) {
                                        $query->where('kriteria_id', $value);
                                    })
                            );
                    }),
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
            'index' => Pages\ListPenilaians::route('/'),
            'create' => Pages\CreatePenilaian::route('/create'),
            // 'view' => Pages\ViewPenilaian::route('/{record}'),
            'edit' => Pages\EditPenilaian::route('/{record}/edit'),
        ];
    }
}