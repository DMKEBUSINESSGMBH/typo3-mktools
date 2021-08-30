<?php

return [
    'frontend' => [
        'dmk/mktools/type-parameter-from-post' => [
            'target' => \DMK\Mktools\Middleware\TypeParameterFromPost::class,
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
