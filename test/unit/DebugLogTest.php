<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests;

use Webiny\Hrc\DebugLog;

class DebugLogTest extends \PHPUnit_Framework_TestCase
{
    public function testBasics()
    {
        $instance = new DebugLog();
        $this->assertInstanceOf('Webiny\Hrc\DebugLog', $instance);

        $instance->addMessage('foo', 'bar');
        $this->assertSame(['foo' => ['bar']], $instance->getLog());

        $instance->addMessage('id', 'val');
        $this->assertSame(['foo' => ['bar'], 'id' => ['val']], $instance->getLog());

        $instance->addMessage('id', 'val2');
        $this->assertSame(['foo' => ['bar'], 'id' => ['val', 'val2']], $instance->getLog());
    }
}