<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests\CacheRules;

use Webiny\Hrc\CacheRules\CacheRule;
use Webiny\Hrc\Request;

class MockCallbacks
{
    public static function returnTrue(Request $r, CacheRule $cr)
    {
        return true;
    }

    public static function returnFalse(Request $r, CacheRule $cr)
    {
        return false;
    }

    public static function returnValue(Request $r, CacheRule $cr)
    {
        return 'value';
    }
}