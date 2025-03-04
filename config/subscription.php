<?php

return [
    'plans' => [
        'basic' => [
            'id' => 1,
            'name' => 'Basic Plan',
            'price' => '29.99',
            'features' => [
                'Up to 100 students',
                'Basic attendance tracking',
                'Report card generation',
                'Email support'
            ]
        ],
        'premium' => [
            'id' => 2,
            'name' => 'Premium Plan',
            'price' => '49.99',
            'features' => [
                'Up to 500 students',
                'Advanced attendance tracking',
                'Custom report cards',
                'Priority email support',
                'SMS notifications',
                'Parent portal'
            ]
        ],
        'enterprise' => [
            'id' => 3,
            'name' => 'Enterprise Plan',
            'price' => '99.99',
            'features' => [
                'Unlimited students',
                'All premium features',
                'API access',
                'Dedicated support',
                'Custom integrations',
                'Data analytics'
            ]
        ]
    ]
];