<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\CacheStorage;

/**
 * Class ArrayCache
 * Note: This cache is not intended for use in production. It's designed to be used in unit tests.
 *
 * @package Webiny\Hrc\CacheStorage
 */
class ArrayCache implements CacheStorageInterface
{
    private $array;

    /**
     * Read the cache for the given key.
     *
     * @param string $key Cache key
     *
     * @return string|bool Cache content, or bool false if the key is not found in the cache.
     */
    public function read($key)
    {
        return isset($this->array[$key]) ? $this->array[$key] : false;
    }

    /**
     * Save the given content into cache.
     *
     * @param string $key     Cache key.
     * @param string $content Content that should be saved.
     * @param string $ttl     Cache time-to-live
     *
     * @return bool
     */
    public function save($key, $content, $ttl)
    {
        $this->array[$key] = $content; // ignore ttl

        return true;
    }

    /**
     * Purge (delete) the given key from cache.
     *
     * @param string $key Cache key that should be deleted.
     *
     * @return bool True if key was found and deleted, otherwise false.
     */
    public function purge($key)
    {
        if (isset($this->array[$key])) {
            unset($this->array[$key]);

            return true;
        }

        return false;
    }

    /**
     * @return int Returns the remaining ttl of the matched cache rule.
     */
    public function getRemainingTtl()
    {
        return 0;
    }
}