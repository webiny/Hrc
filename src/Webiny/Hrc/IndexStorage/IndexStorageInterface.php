<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\IndexStorage;

/**
 * Interface IndexStorageInterface - every index storage must implement this interface.
 *
 * @package Webiny\Hrc\IndexStorage
 */
interface IndexStorageInterface
{
    /**
     * Save the given entry into index.
     *
     * @param string $key  Cache key.
     * @param array  $tags List of tags attached to the entry.
     *
     * @return bool True if save was successful, otherwise false.
     */
    public function save($key, array $tags);

    /**
     * Removes the index entry for the given cache key.
     *
     * @param string $key
     *
     * @return bool True if delete was successful, otherwise false.
     */
    public function deleteEntryByKey($key);

    /**
     * Delete all index entries that match the given list of tags.
     * Note: Only entries that match all tags will be deleted.
     *
     * @param array $tags
     *
     * @return bool True if save was successful, otherwise false.
     */
    public function deleteEntryByTags(array $tags);

    /**
     * Return a list of cache keys that match the given tags.
     * Note: Only entries that match all tags will be returned.
     *
     * @param array $tags
     *
     * @return array|bool List of cache keys, or false if nothing matched the query.
     */
    public function selectByTags(array $tags);

}