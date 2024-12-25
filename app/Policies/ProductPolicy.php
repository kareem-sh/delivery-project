<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function before(User $user){
        return $user->role == 'admin' ? true : null;
    }
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, Product $product): bool
    {
        return true;
    }
    public function create(User $user): bool
    {
        return $user->role =='store_manager' ;
    }
    public function update(User $user, Product $product): bool
    {
        return $user->role =='store_manager' && $user->store->id == $product->store->id;
    }
    public function delete(User $user, Product $product): bool
    {
        return $user->role =='store_manager' && $user->store->id == $product->store->id;
    }
}