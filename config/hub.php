<?php

return [
    'disk'           => env('HUB_DISK', 'local'),
    'max_upload_kb'  => (int) env('HUB_MAX_UPLOAD_KB', 102400),
    'min_password'   => (int) env('HUB_MIN_PASSWORD', 6),
    'admin_password' => env('HUB_ADMIN_PASSWORD'),
];