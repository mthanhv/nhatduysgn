<?php

return [
    'payload' => [
        'max_size' => 1024 * 1024,
        // Product specification tables need deeper rich-text nesting.
        'max_nesting_depth' => 20,
        'max_calls' => 50,
        'max_components' => 200,
    ],
];
