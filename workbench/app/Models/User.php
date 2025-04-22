<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Workbench\Workbench\Database\Factories\UserFactory;
use YourVendor\PackageName\Traits\Taggable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use Taggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
      'name',
      'email',
      'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
      'password',
      'remember_token',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
        ];
    }

    protected function taggable(): array
    {
        return [
          'name',
          'role' => fn () => $this->role->name,
          'email' => 'email',
          'age' => null,
          'reverse_name' => fn () => strrev($this->name),
          'pets_name' => fn () => $this->pets->pluck('name')->join(', ', ' and '),
        ];
    }

    protected function optionalTags()
    {
        return [
          'age',
        ];
    }
}
