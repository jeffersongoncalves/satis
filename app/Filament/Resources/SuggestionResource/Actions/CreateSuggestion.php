<?php

namespace App\Filament\Resources\SuggestionResource\Actions;

use App\Filament\Resources\SuggestionResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Form;

class CreateSuggestion extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Faça uma sugestão')
            ->icon('heroicon-o-sparkles')
            ->color('primary');

        $this
            ->modalDescription('Sugira o próximo produto que você gostaria de comprar conosco.')
            ->modalSubmitActionLabel('Enviar sugestão');

        $this->form(
            fn (Form $form): Form => SuggestionResource::form($form)
        );

        $this->action(function (Action $action, array $data): void {
            try {
                tap(
                    auth()->user(),
                    fn (User $user) => $user->suggestions()->create($data)
                );

                $action->success();
            } catch (\Exception $e) {
                $action->failure();
            }
        });

        $this
            ->successNotificationTitle('Sugestão enviada')
            ->failureNotificationTitle('Ocorreu um erro ao enviar a sugestão');

        $this->visible(
            fn () => auth()->check()
        );
    }
}
