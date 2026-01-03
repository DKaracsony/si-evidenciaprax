<?php

return [
    'allowed_ips' => array_filter(
        array_map(
            'trim',
            explode(',', env('EXTERNAL_API_ALLOWED_IPS', ''))
        )
    ),
];
