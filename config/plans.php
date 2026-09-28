<?php

/*
|--------------------------------------------------------------------------
| Plans
|--------------------------------------------------------------------------
|
| Each paid plan maps to a recurring Stripe Price (test mode ids start with
| "price_"). Limits are enforced by the application; null means unlimited.
| Prices are in cents of CASHIER_CURRENCY and are only used for display.
|
*/

return [

    'default' => 'free',

    'plans' => [

        'free' => [
            'name' => 'Free',
            'price_id' => null,
            'monthly_price' => 0,
            'limits' => [
                'members' => 3,
                'projects' => 3,
            ],
            'features' => [
                'Up to 3 members',
                'Up to 3 projects',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price_id' => env('STRIPE_PRICE_PRO'),
            'monthly_price' => 4900,
            'limits' => [
                'members' => 10,
                'projects' => 50,
            ],
            'features' => [
                'Up to 10 members',
                'Up to 50 projects',
                'Billing portal',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'price_id' => env('STRIPE_PRICE_BUSINESS'),
            'monthly_price' => 14900,
            'limits' => [
                'members' => null,
                'projects' => null,
            ],
            'features' => [
                'Unlimited members',
                'Unlimited projects',
                'Priority support',
            ],
        ],

    ],

];
