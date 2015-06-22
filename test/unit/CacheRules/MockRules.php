<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */

$mockRules = [
    'AdvancedMatch'                  => [
        'Ttl'   => 100,
        'Tags'  => ['advanced', 'one'],
        'Match' => [
            'Url'      => '/simple/url/([\w]+)/page/([\d]+)/(yes|no)/',
            'Cookie'   => [
                'X-Cart'          => 'cart value (\d+) currency ([\w]{2})',
                'X-CacheByCookie' => 'yes'
            ],
            'Header'   => [
                'X-Cache-Me'     => 'foo (\w+)',
                'X-Cache-Header' => '*'
            ],
            'Query'    => [
                'Cache'   => true,
                'someVal' => 'true',
                'foo'     => '*'
            ],
            'Callback' => [
                'Webiny\Hrc\UnitTests\CacheRules\MockCallbacks::returnValue'
            ]
        ]
    ],
    'SimpleUrlCustomCallbackFalse'   => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'callback'],
        'Match' => [
            'Url'      => '/simple/url/custom-callback/false',
            'Callback' => [
                'Webiny\Hrc\UnitTests\CacheRules\MockCallbacks::returnFalse'
            ]
        ]
    ],
    'SimpleUrlCustomCallbackValue'   => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'callback'],
        'Match' => [
            'Url'      => '/simple/url/custom-callback/value',
            'Callback' => [
                'Webiny\Hrc\UnitTests\CacheRules\MockCallbacks::returnValue'
            ]
        ]
    ],
    'SimpleUrlCustomCallbackTrue'    => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'callback'],
        'Match' => [
            'Url'      => '/simple/url/custom-callback/true',
            'Callback' => [
                'Webiny\Hrc\UnitTests\CacheRules\MockCallbacks::returnTrue'
            ]
        ]
    ],
    'AdvancedUrlRegexQueryRegex'     => [
        'Ttl'   => 100,
        'Tags'  => ['advanced', 'url', 'query'],
        'Match' => [
            'Url'   => '/(\w+)/(\w+)/',
            'Query' => [
                'X-Cache-Me' => 'cart value (\d+) currency ([\w]{2})'
            ]
        ]
    ],
    'SimpleUrlQueryRegex'            => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'query'],
        'Match' => [
            'Url'   => '/simple/url',
            'Query' => [
                'X-Cache-Me' => 'foo (\w+)'
            ]
        ]
    ],
    'SimpleUrlQueryValue'            => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'query'],
        'Match' => [
            'Url'   => '/simple/url',
            'Query' => [
                'X-Cache-Me' => 'exact value'
            ]
        ]
    ],
    'SimpleUrlQueryWildcardPartial'  => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'query'],
        'Match' => [
            'Url'   => '/simple/url/wildcard-query',
            'Query' => [
                'X-Cache-Me' => 'some *'
            ]
        ]
    ],
    'SimpleUrlQueryWildcard'         => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'query'],
        'Match' => [
            'Url'   => '/simple/url/wildcard-query',
            'Query' => [
                'X-Cache-Me' => '*'
            ]
        ]
    ],
    'SimpleUrlQuery'                 => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'query'],
        'Match' => [
            'Url'   => '/simple/url',
            'Query' => [
                'X-Cache-Me' => true
            ]
        ]
    ],
    'AdvancedUrlRegexCookieRegex'    => [
        'Ttl'   => 100,
        'Tags'  => ['advanced', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/(\w+)/(\w+)/',
            'Cookie' => [
                'X-Cache-Me' => 'cart value (\d+) currency ([\w]{2})'
            ]
        ]
    ],
    'SimpleUrlCookieRegex'           => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/simple/url',
            'Cookie' => [
                'X-Cache-Me' => 'foo (\w+)'
            ]
        ]
    ],
    'SimpleUrlCookieValue'           => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/simple/url',
            'Cookie' => [
                'X-Cache-Me' => 'exact value'
            ]
        ]
    ],
    'SimpleUrlCookieWildcardPartial' => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/simple/url/wildcard-cookie',
            'Cookie' => [
                'X-Cache-Me' => 'some *'
            ]
        ]
    ],
    'SimpleUrlCookieWildcard'        => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/simple/url/wildcard-cookie',
            'Cookie' => [
                'X-Cache-Me' => '*'
            ]
        ]
    ],
    'SimpleUrlCookie'                => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'cookie'],
        'Match' => [
            'Url'    => '/simple/url',
            'Cookie' => [
                'X-Cache-Me' => true
            ]
        ]
    ],
    'AdvancedUrlRegexHeaderRegex'    => [
        'Ttl'   => 100,
        'Tags'  => ['advanced', 'url', 'header'],
        'Match' => [
            'Url'    => '/(\w+)/(\w+)/',
            'Header' => [
                'X-Cache-Me' => 'cart value (\d+) currency ([\w]{2})'
            ]
        ]
    ],
    'SimpleUrlHeaderRegex'           => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'header'],
        'Match' => [
            'Url'    => '/simple/url',
            'Header' => [
                'X-Cache-Me' => 'foo (\w+)'
            ]
        ]
    ],
    'SimpleUrlHeaderValue'           => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'header'],
        'Match' => [
            'Url'    => '/simple/url',
            'Header' => [
                'X-Cache-Me' => 'exact value'
            ]
        ]
    ],
    'SimpleUrlHeaderWildcardPartial' => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'header'],
        'Match' => [
            'Url'    => '/simple/url/wildcard-header',
            'Header' => [
                'X-Cache-Me' => 'some *'
            ]
        ]
    ],
    'SimpleUrlHeaderWildcard'        => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'header'],
        'Match' => [
            'Url'    => '/simple/url/wildcard-header',
            'Header' => [
                'X-Cache-Me' => '*'
            ]
        ]
    ],
    'SimpleUrlHeader'                => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url', 'header'],
        'Match' => [
            'Url'    => '/simple/url',
            'Header' => [
                'X-Cache-Me' => true
            ]
        ]
    ],
    'AdvancedRegex'                  => [
        'Ttl'   => 100,
        'Tags'  => ['advanced', 'wildcard'],
        'Match' => [
            'Url' => '/simple/url/([\w]+)/page/([\d]+)/(yes|no)/'
        ]
    ],
    'SimpleRegex'                    => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'wildcard'],
        'Match' => [
            'Url' => '/simple/url/([\w]+)'
        ]
    ],
    'SimpleWildcard'                 => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'wildcard'],
        'Match' => [
            'Url' => '/simple/url/*'
        ]
    ],
    'SimpleUrl'                      => [
        'Ttl'   => 100,
        'Tags'  => ['simple', 'url'],
        'Match' => [
            'Url' => '/simple/url'
        ]
    ],
    'Default'                        => [
        'Ttl'   => 60,
        'Tags'  => ['default'],
        'Match' => [
            'Url' => '*'
        ]
    ]
];

return $mockRules;