<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\CacheStorage;

use MongoDB\Model\CollectionInfo;
use Webiny\Component\Mongo\Index\SingleIndex;

/**
 * Class Mongo
 * MongoDb cache storage.
 *
 * @package Webiny\Hrc\CacheStorage
 */
class Mongo implements CacheStorageInterface
{

    CONST collection = 'HrcCacheStorage';

    /**
     * @var \Webiny\Component\Mongo\Mongo
     */
    private $mongoInstance;

    /**
     * Mongo constructor.
     *
     * @param \Webiny\Component\Mongo\Mongo $mongoInstance Webiny MongoDb connection instance.
     *
     */
    public function __construct($mongoInstance)
    {
        $this->mongoInstance = $mongoInstance;
    }

    /**
     * Read the cache for the given key.
     *
     * @param string $key Cache key
     *
     * @return string|bool Cache content, or bool false if the key is not found in the cache.
     */
    public function read($key)
    {
        $result = $this->mongoInstance->findOne(self::collection, ['key' => $key]);

        if (is_object($result) && isset($result->content)) {
            return $result->content;
        }

        return false;
    }

    /**
     * Save the given content into cache.
     *
     * @param string $key Cache key.
     * @param string $content Content that should be saved.
     * @param int    $ttl Cache time-to-live
     *
     * @return bool
     */
    public function save($key, $content, $ttl)
    {
        $result = $this->mongoInstance->update(self::collection, ['key' => $key],
            ['$set' => ['key' => $key, 'ttl' => (time() + $ttl), 'content' => $content]], ['upsert' => true]);

        if (is_object($result) && isset($key)) {
            return true;
        }

        return false;
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
        $this->mongoInstance->findOneAndDelete(self::collection, ['key' => $key]);

        return true;
    }

    /**
     * Installs the required mongo collection for cache storage and creates the required indexes.
     *
     * @return bool
     */
    public function installCollections()
    {
        $collections = $this->mongoInstance->listCollections();

        foreach ($collections as $collection) {
            /* @var $collection CollectionInfo */
            if ($collection->getName() == self::collection) {
                return true;
            }
        }

        $this->mongoInstance->createCollection(self::collection);
        $this->mongoInstance->createIndex(self::collection, new SingleIndex('key', 'key', false, true));

        return true;

    }
}