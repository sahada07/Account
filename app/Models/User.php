<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Role check methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isAccount()
    {
        return $this->role === 'accountant';
    }

    // Permission check methods
    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canDelete()
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public function canEdit()
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public function canCreate()
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public function canViewDetails()
    {
        return true; // All roles can view details
    }
}