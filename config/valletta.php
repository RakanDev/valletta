<?php

return [
    // Disk used for product images: "public" locally, "r2" on Railway.
    'media_disk' => env('MEDIA_DISK', 'public'),

    'locales' => ['ar', 'en'],
    'default_locale' => env('APP_LOCALE', 'ar'),

    // Initial admin account (created by the seeder).
    'admin_email' => env('ADMIN_EMAIL', 'admin@valletta.jo'),
    'admin_password' => env('ADMIN_PASSWORD'),

    'order_prefix' => 'VL-',

    // Jordan governorates [key => [ar, en]]
    'cities' => [
        'amman' => ['عمّان', 'Amman'],
        'zarqa' => ['الزرقاء', 'Zarqa'],
        'irbid' => ['إربد', 'Irbid'],
        'balqa' => ['البلقاء (السلط)', 'Balqa (Salt)'],
        'madaba' => ['مادبا', 'Madaba'],
        'jerash' => ['جرش', 'Jerash'],
        'ajloun' => ['عجلون', 'Ajloun'],
        'mafraq' => ['المفرق', 'Mafraq'],
        'karak' => ['الكرك', 'Karak'],
        'tafilah' => ['الطفيلة', 'Tafilah'],
        'maan' => ['معان', "Ma'an"],
        'aqaba' => ['العقبة', 'Aqaba'],
    ],
];
