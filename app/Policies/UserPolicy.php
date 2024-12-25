<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function before(User $user){
        return $user->role == 'admin' ? true : null;
    }
    public function viewAny(User $user): bool
    {
        return false;
    }
    public function view(User $user, User $model): bool
    {
        return false;
    }
    public function create(User $user): bool
    {
        return false;
    }
    public function update(User $user, User $model): bool
    {
        return $user->id == $model->id;
    }
    public function delete(User $user, User $model): bool
    {
        return $user->id == $model->id;
    }
}
