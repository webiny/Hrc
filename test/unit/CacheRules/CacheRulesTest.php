<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests\CacheRules;

use Webiny\Hrc\CacheStorage\ArrayCache;
use Webiny\Hrc\Hrc;
use Webiny\Hrc\IndexStorage\ArrayIndex;
use Webiny\Hrc\Request;

class CacheRulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hrc
     */
    public $hrc;

    public function setUp()
    {
        $cache = new ArrayCache();
        $index = new ArrayIndex();
        $rules = include __DIR__ . '/MockRules.php';

        $this->hrc = new Hrc($rules, $cache, $index);

        require_once __DIR__ . '/MockCallbacks.php';
    }

    /**
     * @param Request $r
     * @param string  $expectedRuleName
     *
     * @dataProvider caseProvider
     */
    public function testCacheRuleMatch(Request $r, $expectedRuleName)
    {
        $this->hrc->setRequest($r);
        $result = $this->hrc->getMatchedRule();

        $this->assertSame($expectedRuleName, $result->getCacheRule()->getName());
    }

    public function caseProvider()
    {
        return [
            [
                new Request('/simple/url/test/page/10/no/', [
                    'X-Cache-Me'     => 'foo bar',
                    'X-Cache-Header' => 'someValue'
                ], [
                    'X-Cart'          => 'cart value 500 currency US',
                    'X-CacheByCookie' => 'yes'
                ], [
                    'Cache'   => '',
                    'someVal' => 'true',
                    'foo'     => 'bar'
                ]),
                'AdvancedMatch'
            ],
            [
                new Request('/simple/url/custom-callback/false'),
                'SimpleWildcard'
                // matches SimpleWildcard since callback returns false, meaning the cache rule will not be matched
            ],
            [
                new Request('/simple/url/custom-callback/value'),
                'SimpleUrlCustomCallbackValue'
            ],
            [
                new Request('/simple/url/custom-callback/true'),
                'SimpleUrlCustomCallbackTrue'
            ],
            [
                new Request('/simple/url/', null, null, ['X-Cache-Me' => 'cart value 100 currency EU']),
                'AdvancedUrlRegexQueryRegex'
            ],
            [
                new Request('/simple/url', null, null, ['X-Cache-Me' => 'foo bar']),
                'SimpleUrlQueryRegex'
            ],
            [
                new Request('/simple/url', null, null, ['X-Cache-Me' => 'exact value']),
                'SimpleUrlQueryValue'
            ],
            [
                new Request('/simple/url/wildcard-query', null, null, ['X-Cache-Me' => 'some value']),
                'SimpleUrlQueryWildcardPartial'
            ],
            [
                new Request('/simple/url/wildcard-query', null, null, ['X-Cache-Me' => 'test value']),
                'SimpleUrlQueryWildcard'
            ],
            [
                new Request('/simple/url', null, null, ['X-Cache-Me' => 'test value']),
                'SimpleUrlQuery'
            ],
            [
                new Request('/simple/url/', null, ['X-Cache-Me' => 'cart value 100 currency EU']),
                'AdvancedUrlRegexCookieRegex'
            ],
            [
                new Request('/simple/url', null, ['X-Cache-Me' => 'foo bar']),
                'SimpleUrlCookieRegex'
            ],
            [
                new Request('/simple/url', null, ['X-Cache-Me' => 'exact value']),
                'SimpleUrlCookieValue'
            ],
            [
                new Request('/simple/url/wildcard-cookie', null, ['X-Cache-Me' => 'some value']),
                'SimpleUrlCookieWildcardPartial'
            ],
            [
                new Request('/simple/url/wildcard-cookie', null, ['X-Cache-Me' => 'test value']),
                'SimpleUrlCookieWildcard'
            ],
            [
                new Request('/simple/url', null, ['X-Cache-Me' => 'test value']),
                'SimpleUrlCookie'
            ],
            [
                new Request('/simple/url/', ['X-Cache-Me' => 'cart value 100 currency EU']),
                'AdvancedUrlRegexHeaderRegex'
            ],
            [
                new Request('/simple/url', ['X-Cache-Me' => 'foo bar']),
                'SimpleUrlHeaderRegex'
            ],
            [
                new Request('/simple/url', ['X-Cache-Me' => 'exact value']),
                'SimpleUrlHeaderValue'
            ],
            [
                new Request('/simple/url/wildcard-header', ['X-Cache-Me' => 'some value']),
                'SimpleUrlHeaderWildcardPartial'
            ],
            [
                new Request('/simple/url/wildcard-header', ['X-Cache-Me' => 'test value']),
                'SimpleUrlHeaderWildcard'
            ],
            [
                new Request('/simple/url', ['X-Cache-Me' => 'test value']),
                'SimpleUrlHeader'
            ],
            [
                new Request('/simple/url/test/page/10/no/'),
                'AdvancedRegex'
            ],
            [
                new Request('/simple/url/test'),
                'SimpleRegex'
            ],
            [
                new Request('/simple/url/some/other/page'),
                'SimpleWildcard'
            ],
            [
                new Request('/simple/url'),
                'SimpleUrl'
            ],
            [
                new Request('/url'),
                'Default'
            ]
        ];
    }
}