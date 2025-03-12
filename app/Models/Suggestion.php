<?php

namespace App\Models;

use App\Observers\SuggestionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(SuggestionVote::class);
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
