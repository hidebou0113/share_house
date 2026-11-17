<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => UserRole::class,
            'move_in_date' => 'immutable_date',
            'move_out_date' => 'immutable_date',
        ];
    }

    /**
     * Determine whether the user is a resident on a Japanese calendar date.
     */
    public function isResidentOn(CarbonInterface $date): bool
    {
        $targetDate = $date->toDateString();

        return $this->move_in_date->toDateString() <= $targetDate
            && ($this->move_out_date === null
                || $targetDate <= $this->move_out_date->toDateString());
    }
}
