<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\IndexStorage;

/**
 * Class ArrayIndex
 * Note: This index is not intended for use in production. It's designed to be used in unit tests.
 *
 * @package Webiny\Hrc\IndexStorage
 */
class ArrayIndex implements IndexStorageInterface
{
    private $array = [
        'Key2Tags' => [],
        'Tags2Key' => []
    ];

    /**
     * Save the given entry into index.
     *
     * @param string $key  Cache key.
     * @param array  $tags List of tags attached to the entry.
     * @param int    $ttl  Unix timestamp until when the cache entry is considered valid.
     *
     * @return bool True if save was successful, otherwise false.
     */
    public function save($key, array $tags, $ttl)
    {
        // !ttl is ignored on this driver
        $this->array['Key2Tags'][$key] = $tags;

        foreach ($tags as $t) {
            $this->array['Tags2Key'][$t][$key] = '';
        }

        return true;
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
        if (isset($this->array['Key2Tags'][$key])) {
            unset($this->array['Key2Tags'][$key]);

            foreach ($this->array['Tags2Key'] as $tag => $tagKeys) {
                if (isset($tagKeys[$key])) {
                    unset($this->array['Tags2Key'][$tag][$key]);
                }
            }
        }

        return false;
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
        foreach ($this->array['Key2Tags'] as $key => $keyTags) {
            $found = true;
            foreach ($tags as $t) {
                if (!array_search($t, $keyTags)) {
                    $found = false;
                }
            }

            if ($found) {
                $this->deleteEntryByKey($key);
            }
        }
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
        $result = [];
        foreach ($this->array['Key2Tags'] as $key => $keyTags) {
            $found = true;
            foreach ($tags as $t) {
                if (array_search($t, $keyTags)===false) {
                    $found = false;
                }
            }

            if ($found) {
                $result[] = $key;
            }
        }

        return $result;
    }
}