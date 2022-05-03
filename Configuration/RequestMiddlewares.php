<?php

return [
    'frontend' => [
        'dmk/mktools/type-parameter-from-post' => [
            'target' => \DMK\Mktools\Middleware\TypeParameterFromPost::class,
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
        'dmk/mktools/content-replacer' => [
            'target' => \DMK\Mktools\Middleware\ContentReplacer::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
