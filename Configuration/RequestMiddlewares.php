<?php

return [
    'frontend' => [
        'webcoast/deferred-image-processing/image-processor' => [
            'target' => WEBcoast\DeferredImageProcessing\Middleware\ImageProcessor::class,
            'after' => [
                'bt3/bt3-core/frontend/post-site-resolver',
            ],
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
        ]
    ]
];
