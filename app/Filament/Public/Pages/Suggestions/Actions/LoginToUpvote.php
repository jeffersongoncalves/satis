<?php

namespace App\Filament\Public\Pages\Suggestions\Actions;

use Filament\Actions\StaticAction;

class LoginToUpvote extends Upvote
{
    public static function getDefaultName(): ?string
    {
        return 'login-to-upvote';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->modal(true)
            ->modalHeading('Faça login')
            ->modalDescription('Você precisa estar logado para votar em uma sugestão.')
            ->modalSubmitActionLabel('Entrar ou Criar uma conta')
            ->modalSubmitAction(
                fn (StaticAction $action) => $action->url(route('filament.admin.auth.login', ['back' => '/']))
            );

        $this->visible(
            fn () => auth()->guest()
        );
    }
}
