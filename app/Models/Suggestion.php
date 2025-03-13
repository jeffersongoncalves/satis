<?php

namespace App\Models;

use App\Enums\SuggestionVisibility;
use App\Observers\SuggestionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use shweshi\OpenGraph\Facades\OpenGraphFacade;

#[ObservedBy(SuggestionObserver::class)]
class Suggestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'image_url',
        'visibility',
        'can_receive_votes',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => SuggestionVisibility::class,
            'can_receive_votes' => 'boolean',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(SuggestionVote::class);
    }

    public function scopePublic(Builder $query): void
    {
        $query->where('visibility', SuggestionVisibility::Public);
    }

    public function toggleVisibility(): void
    {
        $this->update([
            'visibility' => match ($this->visibility) {
                SuggestionVisibility::Public => SuggestionVisibility::Private,
                SuggestionVisibility::Private => SuggestionVisibility::Public,
            },
        ]);
    }

    public function upvote(User $user): void
    {
        $this->votes()->createOrFirst(['user_id' => $user->id]);
    }

    public function downvote(User $user): void
    {
        $this->votes()->where('user_id', $user->id)->delete();
    }

    public function upvoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function fetchImage(): void
    {
        $graph = OpenGraphFacade::fetch($this->url, true);

        $this->updateQuietly([
            'image_url' => $graph['image'] ?? null,
        ]);
    }
}
