<?php

//Sandbox
defined('business') or define('business', 'sb-uefcv23946367@business.example.com');

//Live
// defined('PAYPAL_LIVE_BUSINESS_EMAIL') or define('PAYPAL_LIVE_BUSINESS_EMAIL', '');
// defined('PAYPAL_CURRENCY') or define('PAYPAL_CURRENCY', 'USD');

return [
    'RESPONSE_CODE'    => [
        'LOGIN_SUCCESS'    => 100,
        'VALIDATION_ERROR' => 102,
        'EXCEPTION_ERROR'  => 103,
        'SUCCESS'          => 200,
    ],
    'CACHE'            => [
        'LANGUAGE' => 'languages',
        'SETTINGS' => 'settings'
    ],
    'DEFAULT_SETTINGS' => [
        ['name' => 'admin_logo', 'value' => 'logo/logo1.png', 'type' => 'file'],
        ['name' => 'favicon', 'value' => 'logo/FavIcon.png', 'type' => 'file'],
        ['name' => 'login_image', 'value' => 'bg/login.jpg', 'type' => 'file'],

        ['name' => 'web_theme_color', 'value' => '#6c2287', 'type' => 'string'],
        ['name' => 'firebase_project_id', 'value' => '', 'type' => 'string'],
        ['name' => 'placeholder_image', 'value' => 'logo/FavIcon.png', 'type' => 'file'],
        ['name' => 'web_logo', 'value' => 'logo/logo1.png', 'type' => 'file'],
    ]
];
