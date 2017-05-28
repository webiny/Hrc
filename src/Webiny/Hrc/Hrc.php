<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Hrc;

use Webiny\Hrc\CacheRules\CacheRule;
use Webiny\Hrc\CacheStorage\CacheStorageInterface;
use Webiny\Hrc\IndexStorage\IndexStorageInterface;

/**
 * Class Hrc - see readme for more information.
 *
 * @package Webiny\Hrc
 */
class Hrc
{
    /**
     * Name of the purge flag
     */
    const H_PURGE = 'X-HRC-Purge';

    /**
     * Name of the debug header
     */
    const H_DEBUG = 'X-HRC-Debug';

    /**
     * Name of the control key header
     */
    const H_CKEY = 'X-HRC-Control-Key';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var CacheStorageInterface
     */
    private $cacheStorage;

    /**
     * @var IndexStorageInterface
     */
    private $indexStorage;

    /**
     * @var bool Should the matched cache keys be purged
     */
    private $purgeFlag = false;

    /**
     * @var string
     */
    private $controlKey = '';

    /**
     * @var DebugLog
     */
    private $log;

    /**
     * @var array
     */
    private $cacheRules;

    /**
     * @var array
     */
    private $cacheRuleMap;

    /**
     * @var array
     */
    private $callbacks = [];


    /**
     * Base constructor.
     *
     * @param array                 $cacheRules List of cache rules.
     * @param CacheStorageInterface $cacheStorage Cache storage instance.
     * @param IndexStorageInterface $indexStorage Index storage instance.
     *
     * @throws HrcException
     */
    public function __construct(array $cacheRules, CacheStorageInterface $cacheStorage, IndexStorageInterface $indexStorage)
    {
        $this->cacheStorage = $cacheStorage;
        $this->indexStorage = $indexStorage;

        $this->setCacheRules($cacheRules);
    }

    /**
     * Register a callback for certain events.
     * Currently supported events: beforeSave, afterSave, beforeRead, afterRead
     *
     * @param EventCallbackInterface $callback Your callback.
     *
     * @throws HrcException
     */
    public function registerCallback(EventCallbackInterface $callback)
    {
        if (!in_array($callback, $this->callbacks)) {
            $this->callbacks[] = $callback;
        }
    }

    /**
     * Get the purge flag value.
     *
     * @return bool
     */
    public function getPurgeFlag()
    {
        return $this->purgeFlag;
    }

    /**
     * Set the purge flag value.
     *
     * @param bool $purgeFlag Should the requests be purged on read, or not.
     */
    public function setPurgeFlag($purgeFlag)
    {
        $this->purgeFlag = (bool)$purgeFlag;
    }

    /**
     * Returns the value of control key.
     *
     * @return string
     */
    public function getControlKey()
    {
        return $this->controlKey;
    }

    /**
     * Set the control key value.
     *
     * @param string $controlKey Control key value.
     */
    public function setControlKey($controlKey)
    {
        $this->controlKey = $controlKey;
    }

    /**
     * Set the Request instance which will be used for matching the cache rules.
     *
     * @param Request $request Request instance.
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the current Request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        if (empty($this->request)) {
            $this->request = new Request();
        }

        return $this->request;
    }

    /**
     * In case you are modifying the $_SERVER object via code, that can have a negative effect
     * on how the cache key is built.
     * In such a case you need to call this method after each change to the $_SERVER object.
     */
    public function flushRequest()
    {
        $this->request = null;
    }

    /**
     * Get a specific cache rule, or all of them.
     *
     * @param null|string $name If provided, only the matching rule will be returned.
     *
     * @return bool|CacheRule
     */
    public function getCacheRule($name = null)
    {
        if (is_null($name)) {
            return $this->cacheRules;
        } else {
            foreach ($this->cacheRules as $r) {
                if ($r->getName() == $name) {
                    return $r;
                }
            }

            return false;
        }
    }

    /**
     * Overwrite the current cache rule list.
     *
     * @param array $cacheRules
     *
     * @throws HrcException
     */
    public function setCacheRules(array $cacheRules)
    {
        $i = 0;
        foreach ($cacheRules as $crName => $cr) {
            if (isset($cr['Ttl']) && isset($cr['Tags']) && isset($cr['Match'])) {
                $this->cacheRules[$i] = new CacheRule($crName, $cr['Ttl'], $cr['Tags'], $cr['Match']);
                $this->cacheRuleMap[$crName] = $i;
            } else {
                throw new HrcException(sprintf('Unable to parse "%s" rule. The rule is missing a definition for one of these attributes: Ttl, Tags or Match',
                    $crName));
            }
            $i++;
        }
    }


    /**
     * Add a cache rule to the end of cache rule list.
     *
     * @param CacheRule $cr Cache rule.
     */
    public function appendRule(CacheRule $cr)
    {
        $this->cacheRules[] = $cr;
    }

    /**
     * Add a cache rule to the beginning of cache rule list.
     *
     * @param CacheRule $cr Cache rule.
     */
    public function prependRule(CacheRule $cr)
    {
        array_unshift($this->cacheRules, $cr);
    }

    /**
     * Try to retrieve the a value from the cache for the given name and current request.
     * Note: If purge flag is set to true, the read method will actually purge the matched cache entry and return false.
     *
     * @param string $name The same name used when saving the cache.
     * @param string $cacheRule The name of the cache rule that should be used, instead of the matched cache rule based on the request.
     *
     * @return bool|string Cache value, or false if the entry was not found.
     */
    public function read($name, $cacheRule = null)
    {
        // initialize log
        $log = new DebugLog();
        $log->addMessage('State', 'Read');

        // check if get
        if (!isset($_SERVER['REQUEST_METHOD']) || strtolower($_SERVER['REQUEST_METHOD']) != 'get') {
            $log->addMessage('CacheRule-Match', 'Only GET requests can be cached.');

            return false;
        }

        // get rule
        if (!($rule = $this->getMatchedRule($cacheRule)) || $rule->getCacheRule()->getTtl() <= 0) {
            $log->addMessage('CacheRule-Match', 'No rule matched the request.');

            return false;
        }
        $log->addMessage('CacheRule-Match', sprintf('%s rule matched the request.', $rule->getCacheRule()->getName()));

        // reads the cache
        $key = $this->createJointKey($name, $rule->getCacheKey());

        // create read payload
        $readPayload = new ReadPayload($key, null, $rule);

        // callback: beforeRead
        foreach ($this->callbacks as $cb) {
            call_user_func_array([$cb, 'beforeRead'], [$readPayload]);
        }

        $log->addMessage('CacheRule-CacheKey', $readPayload->getKey());
        if (!($cache = $this->cacheStorage->read($readPayload->getKey()))) {
            $log->addMessage('CacheStorage-Read', 'MISS');

            return false;
        }
        $log->addMessage('CacheStorage-Read', 'HIT');

        // if purge flag is true, we need to clear the cache and return false
        if ($this->purgeFlag || $this->canPurge() || $readPayload->getPurgeFlag()) {
            $log->addMessage('CacheRule-Purge', 'A purge was requested. Purging cache and returning false on read.');
            $this->purgeByCacheKey($readPayload->getKey());

            return false;
        }

        $this->log = $log;

        // callback: afterRead
        $readPayload->setContent($cache);
        foreach ($this->callbacks as $cb) {
            call_user_func_array([$cb, 'afterRead'], [$readPayload]);
        }

        // return cache content
        return $readPayload->getContent();
    }

    /**
     * If read was successful, the method will return the remaining ttl of the matched cache content.
     *
     * @return int
     */
    public function getRemainingTtl()
    {
        return $this->cacheStorage->getRemainingTtl();
    }

    /**
     * Save a value into cache.
     * In case if no cache rule was matched, false is returned.
     *
     *
     * @param string $name Name that will be used to construct the cache key.
     * @param string $content Content that should be save, must be a string.
     * @param string $cacheRule The name of the cache rule that should be used, instead of the matched cache rule based on the request.
     * @param array  $cacheTagsAppend Optionally you can append additional tags to the matched rule for this request.
     *                                These tags can be used later to purge the cache.
     *
     * @return bool|string A cache key is returned if save was successful, otherwise false.
     * @throws HrcException
     */
    public function save($name, $content, $cacheRule = null, $cacheTagsAppend = [])
    {
        // initialize log
        $log = new DebugLog();
        $log->addMessage('State', 'Save');

        // check if get
        if (!isset($_SERVER['REQUEST_METHOD']) || strtolower($_SERVER['REQUEST_METHOD']) != 'get') {
            $log->addMessage('CacheRule-Match', 'Only GET requests can be cached.');

            return false;
        }

        // get rule
        if (!($rule = $this->getMatchedRule($cacheRule)) || $rule->getCacheRule()->getTtl() <= 0) {
            $log->addMessage('CacheRule-Match', 'No rule matched the request.');

            return false;
        }
        $log->addMessage('CacheRule-Match', sprintf('%s rule matched the request.', $rule->getCacheRule()->getName()));

        // append tags
        $rule->getCacheRule()->appendTags($cacheTagsAppend);

        // create key
        $key = $this->createJointKey($name, $rule->getCacheKey());

        // create save payload instance
        $savePayload = new SavePayload($key, $content, $rule);

        // callback: beforeSave
        foreach ($this->callbacks as $cb) {
            call_user_func_array([$cb, 'beforeSave'], [$savePayload]);
        }

        $log->addMessage('CacheRule-CacheKey', $savePayload->getKey());
        $saved = $this->cacheStorage->save($savePayload->getKey(), $savePayload->getContent(),
            $savePayload->getRule()->getCacheRule()->getTtl());

        // update the index
        if ($saved) {
            $this->indexStorage->save($savePayload->getKey(), $savePayload->getRule()->getCacheRule()->getTags(),
                ($savePayload->getRule()->getCacheRule()->getTtl() + time()));
            $log->addMessage('CacheStorage-Save', 'Cache saved.');
        } else {
            throw new HrcException('There has been an error while trying to save the cache.');
        }

        $this->log = $log;

        // when saved, return cache key
        // callback: afterSave
        foreach ($this->callbacks as $cb) {
            call_user_func_array([$cb, 'afterSave'], [$savePayload]);
        }

        return $savePayload->getKey();
    }

    /**
     * Purge the given cache key.
     *
     * @param string $cacheKey Cache key that should be purged.
     *
     * @return bool True if purge was successful, otherwise false.
     */
    public function purgeByCacheKey($cacheKey)
    {
        // initialize log
        $log = new DebugLog();
        $log->addMessage('State', 'Purge by cache key');

        // remove the key from index
        $this->indexStorage->deleteEntryByKey($cacheKey);

        // purges from cache storage
        return $this->cacheStorage->purge($cacheKey);
    }

    /**
     * Purge all cache entries that match the given tags.
     * Note: AND condition is used between tags, so all tags must match for in order to purge a cache key.
     *
     * @param array|string $tags Single tag or a list of tags used to purge the cache.
     *
     * @return bool
     */
    public function purgeByTag($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        // initialize log
        $log = new DebugLog();
        $log->addMessage('State', 'Purge by cache tag');

        // get caches from index storage
        $cacheKeys = $this->indexStorage->selectByTags($tags);
        if (!is_array($cacheKeys)) {
            return false;
        }

        foreach ($cacheKeys as $ck) {
            // purge from cache storage
            $this->cacheStorage->purge($ck);
        }

        // purge the index storage
        $this->indexStorage->deleteEntryByTags($tags);

        return true;
    }

    /**
     * Returns the debug log.
     * This is useful when debugging things.
     * Note: the header check flag will automatically validate the Request object for the control key and the debug flag.
     * Only if they are met, the debug log will be returned. If you wish to skip this validation, just pass false for header check.
     *
     * @return bool|array A list of debug messages, or false if validation check failed or there is nothing in the log.
     */
    public function getDebugFlags($headerCheck = true)
    {
        if ($headerCheck && !$this->canDebug()) {
            return false;
        }

        if (is_object($this->log)) {
            return $this->log->getLog();
        }

        return false;
    }

    /**
     * Returns a MatchedRule instance of the matched cache rule.
     *
     * @return bool|MatchedRule MatchedRule instance. or false if no cache rule matched the request.
     */
    public function getMatchedRule($cacheRule = null)
    {
        $request = $this->getRequest();

        if (!empty($cacheRule)) {
            if (isset($this->cacheRuleMap[$cacheRule])) {
                return ($this->cacheRules[$this->cacheRuleMap[$cacheRule]]);
            }
        } else {
            foreach ($this->cacheRules as $cr) {
                if (($cacheKey = $cr->match($request))) {
                    return new MatchedRule($cr, $cacheKey);
                }
            }
        }


        return false;
    }

    /**
     * Joins the given cache key and the name into a single cache key.
     *
     * @param string $name Cache name.
     * @param string $cacheKey Cache key generated from the cache rule.
     *
     * @return string New cache key.
     */
    private function createJointKey($name, $cacheKey)
    {
        return md5($name . '-' . $cacheKey);
    }

    /**
     * Checks if user can purge the cache by validating the control key and purge flag inside the request headers.
     *
     * @return bool
     */
    private function canPurge()
    {
        // check if purge header exists
        if (!$this->request->matchHeader(self::H_PURGE)) {
            return false;
        }

        if ($this->controlKey == '') {
            return true; // everybody can purge
        }

        // only users with valid control key can purge
        if ($this->request->matchHeader(self::H_CKEY, $this->controlKey)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user can retrieve debug log by validating the control key and debug flag inside the request headers.
     *
     * @return bool
     */
    private function canDebug()
    {
        // check if debug header exists
        if (!$this->request->matchHeader(self::H_DEBUG)) {
            return false;
        }

        if ($this->controlKey == '') {
            return true; // everybody can debug
        }

        // only users with valid control key can debug
        if ($this->request->matchHeader(self::H_CKEY, $this->controlKey)) {
            return true;
        }

        return false;
    }
}
