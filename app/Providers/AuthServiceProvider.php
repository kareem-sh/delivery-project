<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Store;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\StorePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Store::class => StorePolicy::class,
        Order::class => OrderPolicy::class,
        Category::class => CategoryPolicy::class
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
