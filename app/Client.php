<?php

declare(strict_types=1);

namespace App;

use App\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'postcode',
        'user_id',
    ];

    protected $appends = [
        'url',
        'can_be_deleted',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getBookingsCountAttribute(): int
    {
        return $this->bookings->count();
    }

    public function getUrlAttribute(): string
    {
        return "/clients/" . $this->id;
    }

    public function getCanBeDeletedAttribute(): bool
    {
        return auth()->check() && auth()->id() === $this->user_id;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        $data['bookings'] = $this->bookings;

        return $data;
    }
}
