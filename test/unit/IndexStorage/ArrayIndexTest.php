<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests\IndexStorage;

use Webiny\Hrc\IndexStorage\ArrayIndex;

class ArrayIndexTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $instance = new ArrayIndex();
        $this->assertInstanceOf('Webiny\Hrc\IndexStorage\ArrayIndex', $instance);
    }

    public function testSaveAndSelectByTags()
    {
        $instance = new ArrayIndex();
        $key = md5('some key');

        $result = $instance->selectByTags(['test']);
        $this->assertSame([], $result);

        $instance->save($key, ['test', 'foo']);
        $result = $instance->selectByTags(['test']);
        $this->assertSame([$key], $result);
        $result = $instance->selectByTags(['foo']);
        $this->assertSame([$key], $result);
        $result = $instance->selectByTags(['foo', 'test']);
        $this->assertSame([$key], $result);
    }

    public function testDeleteEntryByKey()
    {
        $instance = new ArrayIndex();
        $key1 = md5('some key');
        $key2 = md5('foo bar');

        $instance->save($key1, ['test']);
        $instance->save($key2, ['test']);

        $result = $instance->selectByTags(['test']);
        $this->assertSame([$key1, $key2], $result);

        $instance->deleteEntryByKey($key2);
        $result = $instance->selectByTags(['test']);
        $this->assertSame([$key1], $result);

        $instance->deleteEntryByKey($key1);
        $result = $instance->selectByTags(['test']);
        $this->assertSame([], $result);
    }

    public function testDeleteEntryByTags()
    {
        $instance = new ArrayIndex();
        $key1 = md5('some key');
        $key2 = md5('foo bar');
        $key3 = md5('bar foo');

        $instance->save($key1, ['test', 'foo']);
        $instance->save($key2, ['test', 'bar']);
        $instance->save($key3, ['test', 'foo']);

        $instance->deleteEntryByTags(['bar']);
        $result = $instance->selectByTags(['test']);
        $this->assertSame([$key1, $key3], $result);

        $instance->deleteEntryByTags(['foo']);
        $result = $instance->selectByTags(['test']);
        $this->assertSame([], $result);
    }

}