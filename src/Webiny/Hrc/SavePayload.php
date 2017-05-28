<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc;

use Webiny\Hrc\CacheRules\CacheRule;

class SavePayload
{
    /**
     * @var string Cache key.
     */
    private $key;

    /**
     * @var string Cache content.
     */
    private $content;

    /**
     * @var MatchedRule
     */
    private $rule;

    public function __construct($key, $content, MatchedRule $rule)
    {
        $this->key = $key;
        $this->content = $content;
        $this->rule;
    }

    /**
     * @return string Cache key.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string Cache content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return MatchedRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param MatchedRule $rule
     */
    public function setRule(MatchedRule $rule)
    {
        $this->rule = $rule;
    }
}