<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests;

use Webiny\Hrc\CacheRules\CacheRule;
use Webiny\Hrc\CacheStorage\ArrayCache;
use Webiny\Hrc\Hrc;
use Webiny\Hrc\IndexStorage\ArrayIndex;
use Webiny\Hrc\Request;

class HrcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hrc
     */
    public $instance;

    public function setUp()
    {
        // mock server
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $cacheRules = [
            'FooBar' => [
                'Ttl'   => 60,
                'Tags'  => ['cacheAll'],
                'Match' => [
                    'Url' => '*'
                ]
            ]
        ];

        $this->instance = new Hrc($cacheRules, new ArrayCache(), new ArrayIndex());
        $this->instance->setRequest(new Request('http://test/url', []));
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Webiny\Hrc\Hrc', $this->instance);
    }

    public function testGetSetPurgeFlag()
    {
        $this->assertFalse($this->instance->getPurgeFlag());
        $this->instance->setPurgeFlag(true);
        $this->assertTrue($this->instance->getPurgeFlag());
    }

    public function testGetSetControlKey()
    {
        $this->assertSame('', $this->instance->getControlKey());
        $this->instance->setControlKey('test key');
        $this->assertSame('test key', $this->instance->getControlKey());
    }

    public function testGetPrependAppendCacheRule()
    {
        $this->assertInstanceOf('Webiny\Hrc\CacheRules\CacheRule', $this->instance->getCacheRule('FooBar'));

        $this->assertFalse($this->instance->getCacheRule('AppendedRule'));
        $appendedRule = new CacheRule('AppendedRule', 100, ['t1'], ['Url' => 'appended-rule/*']);
        $this->instance->appendRule($appendedRule);
        $this->assertSame($appendedRule, $this->instance->getCacheRule('AppendedRule'));

        $this->assertFalse($this->instance->getCacheRule('PrependedRule'));
        $prependedRule = new CacheRule('PrependedRule', 100, ['t1'], ['Url' => 'prepended-rule/*']);
        $this->instance->prependRule($prependedRule);
        $this->assertSame($prependedRule, $this->instance->getCacheRule('PrependedRule'));

        $rules = $this->instance->getCacheRule();
        $this->assertSame(3, count($rules));
        $this->assertSame('PrependedRule', $rules[0]->getName());
        $this->assertSame('FooBar', $rules[1]->getName());
        $this->assertSame('AppendedRule', $rules[2]->getName());
    }

    public function testSetGetRequest()
    {
        $r = new Request('http://test/url', []);
        $this->instance->setRequest($r);
        $r2 = $this->instance->getRequest();
        $this->assertSame($r, $r2);
    }

    public function testSaveRead()
    {
        $this->assertFalse($this->instance->read('fooBar'));
        $key = $this->instance->save('fooBar', 'some content');
        $this->assertSame(32, strlen($key));

        $this->assertSame('some content', $this->instance->read('fooBar'));
    }

    public function testPurgeByCacheKey()
    {
        $key = $this->instance->save('fooBar', 'some content');
        $this->assertNotFalse($key);
        $result = $this->instance->read('fooBar');
        $this->assertSame('some content', $result);

        $this->instance->purgeByCacheKey($key);
        $result = $this->instance->read('fooBar');
        $this->assertFalse($result);
    }

    public function testPurgeByTag()
    {
        $this->assertFalse($this->instance->read('fooBar'));
        $key = $this->instance->save('fooBar', 'some content');
        $this->assertSame(32, strlen($key));
        $this->instance->purgeByTag(['cacheAll']);
        $result = $this->instance->read('fooBar');
        $this->assertFalse($result);

        $key = $this->instance->save('fooBar', 'some content', null, ['customTag']);
        $this->assertSame(32, strlen($key));
        $this->instance->purgeByTag(['customTag']);
        $result = $this->instance->read('fooBar');
        $this->assertFalse($result);

        $key = $this->instance->save('fooBar', 'some content', null, ['customTag']);
        $this->assertSame(32, strlen($key));
        $this->instance->purgeByTag(['someOtherTag']);
        $result = $this->instance->read('fooBar');
        $this->assertNotFalse($result);

        $this->instance->purgeByTag(['cacheAll', 'customTag', 'someOtherTag']);
        $result = $this->instance->read('fooBar');
        $this->assertNotFalse($result);

        $this->instance->purgeByTag(['cacheAll', 'customTag']);
        $result = $this->instance->read('fooBar');
        $this->assertFalse($result);
    }

    public function testGetMatchedRule()
    {
        $rule = $this->instance->getMatchedRule();
        $this->assertInstanceOf('\Webiny\HRC\MatchedRule', $rule);
    }
}