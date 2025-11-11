<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Globaly make sure that users see only clients that belong to them or are assigned to them.
 */
class UserScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->when(
            auth()->check(),
            fn (Builder $query) => $query->where(function (Builder $q) {
                $q->where('user_id', auth()->id())
                    ->orWhereHas('assignedUsers', fn (Builder $subQuery) => $subQuery->where('users.id', auth()->id()));
            })
        );
    }
}
