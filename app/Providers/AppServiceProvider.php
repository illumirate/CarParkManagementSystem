<?php
//  Author: Ng Ian Kai

namespace App\Providers;

use App\Adapters\StripePaymentAdapter;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ADAPTER PATTERN: Bind the Target interface to the Adapter
        $this->app->bind(PaymentGatewayInterface::class, StripePaymentAdapter::class);
    }

    public function boot(): void
    {
        // Define password policy requirements
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}
