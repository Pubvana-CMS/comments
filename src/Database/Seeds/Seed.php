<?php

return [
    'install' => [
        [
            'table' => 'settings',
            'rows'  => [
                ['class' => 'Comments', 'key' => 'comments_enabled',      'value' => '1',       'type' => 'boolean', 'title' => 'Comments Enabled',      'description' => 'Master toggle for the entire comment system',  'context' => 'self'],
                ['class' => 'Comments', 'key' => 'allow_guest_comments', 'value' => '0',       'type' => 'boolean', 'title' => 'Allow Guest Comments',  'description' => 'Allow non-logged-in users to post comments',   'context' => 'self'],
                ['class' => 'Comments', 'key' => 'default_status',       'value' => 'pending',  'type' => 'string',  'title' => 'Default Status',        'description' => 'Status for new comments: approved or pending', 'context' => 'self'],
                ['class' => 'Comments', 'key' => 'max_nesting_depth',    'value' => '3',        'type' => 'integer', 'title' => 'Max Nesting Depth',     'description' => 'Maximum reply depth for threaded comments',    'context' => 'self'],
                ['class' => 'Comments', 'key' => 'captcha_provider',     'value' => 'none',     'type' => 'string',  'title' => 'Captcha Provider',      'description' => 'Captcha provider: hcaptcha, recaptcha, or none', 'context' => 'self'],
                ['class' => 'Comments', 'key' => 'captcha_site_key',     'value' => '',         'type' => 'string',  'title' => 'Captcha Site Key',      'description' => 'Public site key for the captcha provider',     'context' => 'self'],
                ['class' => 'Comments', 'key' => 'captcha_secret_key',   'value' => '',         'type' => 'string',  'title' => 'Captcha Secret Key',    'description' => 'Secret key for the captcha provider',          'context' => 'self'],
            ],
        ],
        [
            'table' => 'auth_permissions',
            'rows'  => [
                ['alias' => 'comments.moderate', 'description' => 'Moderate comments (approve, reject, delete)'],
            ],
        ],
    ],
];
