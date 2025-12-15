<?php

namespace App\Providers;

use App\Adapters\StripePaymentAdapter;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ADAPTER PATTERN: Bind the Target interface to the Adapter
        $this->app->bind(PaymentGatewayInterface::class, StripePaymentAdapter::class);
    }

    public function boot(): void
    {
        //
    }
}
