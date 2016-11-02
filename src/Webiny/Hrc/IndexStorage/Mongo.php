<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\IndexStorage;

use MongoDB\Model\CollectionInfo;
use Webiny\Component\Mongo\Index\SingleIndex;

/**
 * Class MongoIndex
 *
 * @package Webiny\Hrc\IndexStorage
 */
class Mongo implements IndexStorageInterface
{
    CONST collection = 'HrcIndexStorage';

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
     * Save the given entry into index.
     *
     * @param string $key Cache key.
     * @param array  $tags List of tags attached to the entry.
     * @param int    $ttl Unix timestamp until when the cache entry is considered valid.
     *
     * @return bool True if save was successful, otherwise false.
     */
    public function save($key, array $tags, $ttl)
    {
        $result = $this->mongoInstance->update(self::collection, ['key' => $key],
            ['$set' => ['key' => $key, 'ttl' => time() + $ttl, 'tags' => $tags]], ['upsert' => true]);


        if (is_object($result) && isset($key)) {
            return true;
        }

        return false;
    }

    /**
     * Removes the index entry for the given cache key.
     *
     * @param string $key
     *
     * @return bool True if delete was successful, otherwise false.
     */
    public function deleteEntryByKey($key)
    {
        $this->mongoInstance->findOneAndDelete(self::collection, ['key' => $key]);

        return true;
    }

    /**
     * Delete all index entries that match the given list of tags.
     * Note: Only entries that match all tags will be deleted.
     *
     * @param array $tags
     *
     * @return bool True if save was successful, otherwise false.
     */
    public function deleteEntryByTags(array $tags)
    {
        $this->mongoInstance->delete(self::collection, ['tags' => ['$in' => $tags]]);

        return true;
    }

    /**
     * Return a list of cache keys that match the given tags.
     * Note: Only entries that match all tags will be returned.
     *
     * @param array $tags
     *
     * @return array|bool List of cache keys, or false if nothing matched the query.
     */
    public function selectByTags(array $tags)
    {
        $result = $this->mongoInstance->find(self::collection, ['tags' => ['$all' => $tags]]);
        
        $result = array_column($result, 'key');
        if (count($result) > 0) {
            return $result;
        }

        return false;
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
        $this->mongoInstance->createIndex(self::collection, new SingleIndex('tags', 'tags', true, false));

        return true;
    }
}