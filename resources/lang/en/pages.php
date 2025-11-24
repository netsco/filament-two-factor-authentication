<?php

return [
    'subheading' => 'Or',
    'challenge' => [
        'action_label' => 'use a recovery code',
        'confirm' => 'Please confirm access to your account by entering the authentication code provided by your authenticator application.',
        'code' => 'Code',
        'error' => 'The provided two factor authentication code was invalid.',
        'notification' => [
            'title' => 'Authentication Failed',
            'body' => 'The code you entered is incorrect or has expired. Please try again with a new code from your authenticator app.',
        ],
    ],
    'recovery' => [
        'action_label' => 'use an authentication code',
        'form_hint' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
        'error' => 'The provided two factor authentication code was invalid.',
        'title' => 'Recovery Code',
    ],
];
