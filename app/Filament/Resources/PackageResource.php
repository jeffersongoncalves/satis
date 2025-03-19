<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Forms\PackageResourceForm;
use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $modelLabel = 'Pacote';

    protected static ?string $pluralModelLabel = 'Pacotes';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Meus Pacotes';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return PackageResourceForm::create($form)->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePackages::route('/'),
            'versions' => Pages\ListPackageVersions::route('/{record}/versions'),
        ];
    }
}
