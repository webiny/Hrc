<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc;

/**
 * Class DebugLog - holds the debug log messages.
 *
 * @package Webiny\Hrc
 */
class DebugLog
{
    /**
     * @var array List of debug messages
     */
    private $log;


    /**
     * Add a message to the debug log.
     *
     * @param string $key   Message key name.
     * @param string $value Message value.
     */
    public function addMessage($key, $value)
    {
        $this->log[$key][] = $value;
    }

    /**
     * Get the debug log.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }
}