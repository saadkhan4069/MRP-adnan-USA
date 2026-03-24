<?php

return [

    /*
    | Full URL to the orders endpoint (no trailing slash required).
    | Example: https://mckesson-seven.vercel.app/api/orders
    */
    'api_url' => env('WEBSITE_ORDERS_API_URL', 'https://mckesson-seven.vercel.app/api/orders'),

    'timeout' => (int) env('WEBSITE_ORDERS_TIMEOUT', 45),

];
