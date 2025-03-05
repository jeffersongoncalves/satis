<?php

namespace App\Providers;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications;
use Filament\Pages;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        Pages\Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notifications\Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };

        Pages\Page::$formActionsAreSticky = true;

        Actions\ActionGroup::configureUsing(
            fn (Actions\ActionGroup $action) => $action->icon('heroicon-o-ellipsis-horizontal')
        );

        Actions\Action::configureUsing(
            fn (Actions\Action $action) => $action
                ->modalWidth(MaxWidth::Medium)
                ->closeModalByClickingAway(false)
        );

        Actions\CreateAction::configureUsing(
            fn (Actions\CreateAction $action) => $action
                ->icon('heroicon-o-plus')
                ->createAnother(false)
        );

        Actions\EditAction::configureUsing(
            fn (Actions\EditAction $action) => $action->icon('heroicon-o-pencil')
        );

        Actions\DeleteAction::configureUsing(
            fn (Actions\DeleteAction $action) => $action->icon('heroicon-o-trash')
        );

        Tables\Table::configureUsing(
            fn (Tables\Table $table) => $table
                ->filtersFormWidth('md')
                ->paginationPageOptions([5, 10, 25, 50])
        );

        Tables\Columns\ImageColumn::configureUsing(
            fn (Tables\Columns\ImageColumn $column) => $column->extraImgAttributes(['loading' => 'lazy'])
        );

        Tables\Columns\TextColumn::configureUsing(
            fn (Tables\Columns\TextColumn $column) => $column
                ->limit(50)
                ->wrap()
        );

        Tables\Actions\Action::configureUsing(
            fn (Tables\Actions\Action $action) => $action
                ->modalWidth(MaxWidth::Medium)
                ->closeModalByClickingAway(false)
        );

        Tables\Actions\CreateAction::configureUsing(
            fn (Tables\Actions\CreateAction $action) => $action
                ->icon('heroicon-o-plus')
                ->createAnother(false)
        );

        Tables\Actions\EditAction::configureUsing(
            fn (Tables\Actions\EditAction $action) => $action->icon('heroicon-o-pencil')
        );

        Tables\Actions\DeleteAction::configureUsing(
            fn (Tables\Actions\DeleteAction $action) => $action->icon('heroicon-o-trash')
        );

        Tables\Filters\SelectFilter::configureUsing(
            fn (Tables\Filters\SelectFilter $filter) => $filter->native(false)
        );

        Forms\Components\Actions\Action::configureUsing(
            fn (Forms\Components\Actions\Action $action) => $action
                ->modalWidth(MaxWidth::Medium)
                ->closeModalByClickingAway(false)
        );

        Forms\Components\ToggleButtons::configureUsing(
            fn (Forms\Components\ToggleButtons $component) => $component
                ->inline()
                ->grouped()
        );

        Forms\Components\Placeholder::configureUsing(
            fn (Forms\Components\Placeholder $component) => $component->columnSpanFull()
        );

        Forms\Components\TextInput::configureUsing(
            fn (Forms\Components\TextInput $component) => $component->minValue(0)
        );

        Forms\Components\Select::configureUsing(
            fn (Forms\Components\Select $component) => $component
                ->native(false)
                ->selectablePlaceholder(fn (Forms\Components\Select $component) => ! $component->isRequired())
                ->searchable(fn (Forms\Components\Select $component) => $component->hasRelationship())
                ->preload(fn (Forms\Components\Select $component) => $component->isSearchable())
        );

        Forms\Components\DateTimePicker::configureUsing(
            fn (Forms\Components\DateTimePicker $component) => $component
                ->seconds(false)
                ->maxDate('9999-12-31T23:59')
        );

        Forms\Components\Repeater::configureUsing(
            fn (Forms\Components\Repeater $component) => $component->deleteAction(
                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
            )
        );

        Forms\Components\Builder::configureUsing(
            fn (Forms\Components\Builder $component) => $component->deleteAction(
                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
            )
        );

        Forms\Components\FileUpload::configureUsing(
            fn (Forms\Components\FileUpload $component) => $component->moveFiles()
        );

        Forms\Components\RichEditor::configureUsing(
            fn (Forms\Components\RichEditor $component) => $component->disableToolbarButtons(['blockquote'])
        );

        Forms\Components\Textarea::configureUsing(
            fn (Forms\Components\Textarea $component) => $component->rows(4)
        );

        Infolists\Components\Actions\Action::configureUsing(
            fn (Infolists\Components\Actions\Action $action) => $action
                ->modalWidth(MaxWidth::Medium)
                ->closeModalByClickingAway(false)
        );
    }
}
