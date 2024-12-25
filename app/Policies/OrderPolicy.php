<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role=="user";
    }
    public function view(User $user,Order $order): bool
    {
        return $user->role=="user" && $user->id==$order->user->id;
    }
    public function create(User $user): bool
    {
        return  $user->role=="user";
    }
    public function update(User $user, Order $order): bool
    {
        return  $user->role=="user" && $user->id==$order->user->id;
    }
    public function delete(User $user, Order $order): bool
    {
        return  $user->role=="user" && $user->id==$order->user->id;
    }
}
