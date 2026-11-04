<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'super_admin' => 'Super Admin',
        'pengaju'     => 'Pengaju',
        'editor'      => 'Editor',
        'publisher'   => 'Publisher',
    ];

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isPengaju(): bool    { return $this->role === 'pengaju'; }
    public function isEditor(): bool     { return $this->role === 'editor'; }
    public function isPublisher(): bool  { return $this->role === 'publisher'; }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? (string) $this->role;
    }
}