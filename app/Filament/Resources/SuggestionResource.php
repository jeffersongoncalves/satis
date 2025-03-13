<?php

namespace App\Filament\Resources;

use App\Enums\SuggestionVisibility;
use App\Filament\Resources\SuggestionResource\Pages;
use App\Models\Suggestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    protected static ?string $modelLabel = 'Sugestão';

    protected static ?string $pluralModelLabel = 'Sugestões';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required(),

                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->required(),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\ToggleButtons::make('can_receive_votes')
                            ->label('Pode receber votos')
                            ->options([
                                true => 'Sim',
                                false => 'Não',
                            ])
                            ->default(true)
                            ->visible(
                                fn () => auth()->user()->isAdmin()
                            ),

                        Forms\Components\ToggleButtons::make('visibility')
                            ->label('Visibilidade')
                            ->options(SuggestionVisibility::class)
                            ->default(SuggestionVisibility::Public)
                            ->visible(
                                fn () => auth()->user()->isAdmin()
                            ),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(false)
                    ->size(40),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibilidade')
                    ->badge()
                    ->action(
                        fn (Suggestion $record) => $record->toggleVisibility()
                    )
                    ->extraCellAttributes([
                        'class' => 'w-0',
                    ]),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('votes_count')
                    ->label('Votos')
                    ->counts('votes'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSuggestions::route('/'),
        ];
    }
}
