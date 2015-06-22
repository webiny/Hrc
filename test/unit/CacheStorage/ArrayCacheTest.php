<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests\CacheStorage;

use Webiny\Hrc\CacheStorage\ArrayCache;

class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $instance = new ArrayCache();
        $this->assertInstanceOf('Webiny\Hrc\CacheStorage\ArrayCache', $instance);
    }

    public function testSaveReadPurge()
    {
        $instance = new ArrayCache();
        $key = md5('some key');

        $result = $instance->read($key);
        $this->assertFalse($result);

        $instance->save($key, 'some value', 100);
        $result = $instance->read($key);
        $this->assertSame('some value', $result);

        $instance->purge($key);
        $result = $instance->read($key);
        $this->assertFalse($result);
    }
}