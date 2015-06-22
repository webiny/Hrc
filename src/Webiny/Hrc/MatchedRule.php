<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc;

use Webiny\Hrc\CacheRules\CacheRule;

/**
 * Class MatchedRule - holds the information about the matched cache rule and generated cache key for that rule.
 *
 * @package Webiny\Hrc
 */
class MatchedRule
{
    /**
     * @var CacheRule The matched cache rule.
     */
    private $cacheRule;

    /**
     * @var string Generated cache key by that cache rule.
     */
    private $cacheKey;


    /**
     * Base constructor.
     *
     * @param CacheRule $cacheRule The matched cache rule instance.
     * @param string    $cacheKey  Generated cache key by that cache rule.
     */
    public function __construct(CacheRule $cacheRule, $cacheKey)
    {
        $this->cacheRule = $cacheRule;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Get the matched cache rule instance.
     *
     * @return CacheRule
     */
    public function getCacheRule()
    {
        return $this->cacheRule;
    }

    /**
     * Get the generated cache key.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
}