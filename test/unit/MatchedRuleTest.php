<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests;


use Webiny\Hrc\CacheRules\CacheRule;
use Webiny\Hrc\MatchedRule;

class MatchedRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testBasics()
    {
        $cacheRule = new CacheRule('testRule', 100, ['test'], ['Url' => '/*']);
        $matchedRule = new MatchedRule($cacheRule, 'test');
        $this->assertInstanceOf('Webiny\Hrc\MatchedRule', $matchedRule);

        $this->assertSame($cacheRule, $matchedRule->getCacheRule());
        $this->assertSame('test', $matchedRule->getCacheKey());
    }
}