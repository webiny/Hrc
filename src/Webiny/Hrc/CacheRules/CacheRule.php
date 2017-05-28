<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\CacheRules;

use Webiny\Hrc\HrcException;
use Webiny\Hrc\Request;

/**
 * Class CacheRule - contains the cache rule information.
 *
 * @package Webiny\Hrc\CacheRules
 */
class CacheRule
{
    /**
     * Name of the `url` parameter in the cache rule.
     */
    const url = 'Url';

    /**
     * Name of the `header` parameter in the cache rule.
     */
    const header = 'Header';

    /**
     * Name of the `cookie` parameter in the cache rule.
     */
    const cookie = 'Cookie';

    /**
     * Name of the `query` parameter in the cache rule.
     */
    const query = 'Query';

    /**
     * Name of the `callback` parameter in the cache rule.
     */
    const callback = 'Callback';

    /**
     * @var string Cache rule name.
     */
    private $name;

    /**
     * @var int Time-to-live, in seconds.
     */
    private $ttl;

    /**
     * @var array List of tags associated to the cache rule.
     */
    private $tags;

    /**
     * @var array List of match conditions.
     */
    private $matchRules;

    /**
     * @var array|null Which query parameters should be included in the url match, regardless of match condition.
     */
    private $queryParamsCacheKey;


    /**
     * Base constructor.
     *
     * @param string     $name                Cache rule name.
     * @param int        $ttl                 Time-to-live, in seconds.
     * @param array      $tags                List of tags associated to the cache rule.
     * @param array      $matchRules          List of match conditions.
     * @param array|null $queryParamsCacheKey Which query parameters should be included in the url match, regardless of match condition.
     *
     * @throws HrcException
     */
    public function __construct($name, $ttl, array $tags, array $matchRules, array $queryParamsCacheKey = null)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->tags = $tags;
        $this->queryParamsCacheKey = $queryParamsCacheKey;
        if (count($tags) < 1) {
            throw new HrcException('A cache rule must contain at least one tag.');
        }
        $this->matchRules = $matchRules;
    }

    /**
     * Check if the rule matches the given Request.
     * If the rule matched, cache key is returned.
     *
     * @param Request $request Request instance that will be used to check if the cache rule matches.
     *
     * @return bool|string Cache key if the rule matched, otherwise false.
     * @throws HrcException
     */
    public function match(Request $request)
    {
        $cacheKey = [];

        // if no match rules are defined, we return false
        if (count($this->matchRules) < 1) {
            return false;
        }

        // url
        if (isset($this->matchRules[self::url])) {
            if (!$request->matchUrl($this->matchRules[self::url])) {
                return false;
            } else {
                $cacheKey[self::url] = $request->getUrl();
            }
        }

        // headers
        if (isset($this->matchRules[self::header])) {
            ksort($this->matchRules[self::header]);
            foreach ($this->matchRules[self::header] as $h => $v) {
                if (is_bool($v)) {
                    if ($v === false && $request->matchHeader($h)) {
                        return false;
                    } else if ($v === true && !$request->matchHeader($h)) {
                        return false;
                    }
                    $cacheKey[self::header][] = $h;
                } else {
                    if (!($value = $request->matchHeader($h, $v))) {
                        return false;
                    } else {
                        $cacheKey[self::header][] = $h . ':' . $value;
                    }
                }
            }
        }

        // query params
        if (isset($this->matchRules[self::query])) {
            ksort($this->matchRules[self::query]);
            foreach ($this->matchRules[self::query] as $q => $v) {
                if (is_bool($v)) {
                    if ($v === false && $request->matchQueryParam($q)) {
                        return false;
                    } else if ($v === true && !$request->matchQueryParam($q)) {
                        return false;
                    }
                    $cacheKey[self::query][] = $q;
                } else {
                    if (!($value = $request->matchQueryParam($q, $v))) {
                        return false;
                    } else {
                        $cacheKey[self::query][] = $q . ':' . $value;
                    }
                }
            }
        }

        // cookies
        if (isset($this->matchRules[self::cookie])) {
            ksort($this->matchRules[self::cookie]);
            foreach ($this->matchRules[self::cookie] as $c => $v) {
                if (is_bool($v)) {
                    if ($v === false && $request->matchCookie($c)) {
                        return false;
                    } else if ($v === true && !$request->matchCookie($c)) {
                        return false;
                    }
                    $cacheKey[self::cookie][] = $c;
                } else {
                    if (!($value = $request->matchCookie($c, $v))) {
                        return false;
                    } else {
                        $cacheKey[self::cookie][] = $c . ':' . $value;
                    }
                }
            }
        }

        // custom callback
        if (isset($this->matchRules[self::callback])) {
            foreach ($this->matchRules[self::callback] as $cb) {

                $callbackData = explode('::', $cb);
                if (count($callbackData) != 2) {
                    throw new HrcException('Invalid callback format. The callback must be in format of className::methodName.');
                }

                if (!($result = call_user_func_array($cb, [$request, $this]))) {
                    return false;
                } else {
                    $cacheKey['callback'][] = $result;
                }
            }
        }

        // if cache key is empty, we return false
        if (count($cacheKey) < 1) {
            return false;
        }

        // if we got to this point, all rules have matched
        // lets create the cache key
        $cKeyVal = '';
        foreach ($cacheKey as $ckk => $ckv) {
            if (!is_array($ckv)) {
                $cKeyVal .= $ckk . '-' . $ckv;
            } else {
                foreach ($ckv as $v) {
                    $cKeyVal .= $ckk . '-' . $v;
                }
            }
        }

        // append missing query params to the key
        if (is_null($this->queryParamsCacheKey) || is_array($this->queryParamsCacheKey)) {
            $headers = $request->getHeaders();
            foreach ($headers as $k => $v) {
                if (is_null($this->queryParamsCacheKey)) {
                    $cKeyVal .= self::header . '-' . $k . ':' . $v;
                } elseif (is_array($this->queryParamsCacheKey) && array_search($k, $this->queryParamsCacheKey)) {
                    $cKeyVal .= self::header . '-' . $k . ':' . $v;
                }
            }
        }

        return md5($cKeyVal);
    }

    /**
     * Get the ttl value.
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Get the list of associated tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Append tags to the current cache rule.
     *
     * @param array $tags
     */
    public function appendTags(array $tags)
    {
        $this->tags = array_merge($tags, $this->tags);
    }

    /**
     * Get the rule name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}