<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\IndexStorage;

use Webiny\Hrc\HrcException;

/**
 * Class FileSystem - index storage that uses file system to store the index information.
 *
 * @package Webiny\Hrc\IndexStorage
 */
class FileSystem implements IndexStorageInterface
{
    /**
     * @var string Absolute path to where the index files should be stored.
     */
    private $indexDir;


    /**
     * @param string $indexDir Absolute path to where the index files should be stored.
     */
    public function __construct($indexDir)
    {
        $this->indexDir = realpath(rtrim($indexDir));
        if (!$this->indexDir) {
            mkdir($this->indexDir, 0755, true);
        }
        $this->indexDir .= DIRECTORY_SEPARATOR;
    }

    /**
     * Save the given entry into index.
     *
     * @param string $key  Cache key.
     * @param array  $tags List of tags attached to the entry.
     * @param int    $ttl  Unix timestamp until when the cache entry is considered valid.
     *
     * @return bool True if save was successful, otherwise false.
     * @throws HrcException
     */
    public function save($key, array $tags, $ttl)
    {
        if (count($tags) < 1) {
            throw new HrcException('You need to provide at least one tag.');
        }

        $index = $this->getIndex($key, $tags);

        // open the index for reading and writing
        $h = fopen($index, 'a+');

        // validate ttl length
        if (strlen($ttl) != 10) {
            throw new HrcException('$ttl must always be 10 digits.');
        }

        // check if we already have the key indexed
        // we read 42 chars because the key is hashed using md5 (32 chars) + 10 chars for ttl
        while (($i = fread($h, 42))) {
            if ($i == $key) {
                return true;
            }
        }

        // if the key is not inside the index, insert it
        fwrite($h, $key);

        // close the handler
        fclose($h);

        return true;
    }

    /**
     * Removes the index entry for the given cache key.
     *
     * @param string $key
     *
     * @return bool True if delete was successful, otherwise false.
     * @throws HrcException
     */
    public function deleteEntryByKey($key)
    {
        $pattern = $this->indexDir . '*' . DIRECTORY_SEPARATOR . substr($key, 0, 2) . DIRECTORY_SEPARATOR . substr($key,
                2, 2) . DIRECTORY_SEPARATOR . substr($key, 4, 2) . DIRECTORY_SEPARATOR . 'index.db';

        $result = glob($pattern);

        if (!$result) {
            return false;
        }

        foreach ($result as $index) {
            // open the index for reading only
            $h = fopen($index, 'r');

            // try to find the key and remove it if found
            // we read 42 chars because the key is hashed using md5 (32 chars) + 10 chars for ttl
            $buffer = '';
            $indexFound = false;
            while (($i = fread($h, 42))) {
                if ($i != $key) {
                    $buffer .= $i;
                } else {
                    $indexFound = true;
                }
            }

            // close the handler
            fclose($h);

            // update the index
            if ($indexFound) {
                file_put_contents($index, $buffer);

                return true;
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
     * @throws HrcException
     */
    public function deleteEntryByTags(array $tags)
    {
        if (count($tags) < 1) {
            throw new HrcException('You need to provide at least one tag.');
        }

        $tagFolder = str_replace($this->indexDir, '',
            rtrim($this->createFolderNameFromTags($tags), DIRECTORY_SEPARATOR));

        // remove index
        array_map('unlink', (glob($this->indexDir . '*' . $tagFolder . '*/*/*/*/index.db') ?: []));

        // remove inner folders
        $levelsToRemove = [
            $this->indexDir . '*' . $tagFolder . '*/*/*/*',
            $this->indexDir . '*' . $tagFolder . '*/*/*',
            $this->indexDir . '*' . $tagFolder . '*/*',
            $this->indexDir . '*' . $tagFolder . '*',
        ];

        // remove indexes
        foreach ($levelsToRemove as $lr) {
            array_map('rmdir', (glob($lr) ?: []));
        }
    }

    /**
     * Return a list of cache keys that match the given tags.
     * Note: Only entries that match all tags will be returned.
     *
     * @param array $tags
     *
     * @return array|bool List of cache keys, or false if nothing matched the query.
     * @throws HrcException
     */
    public function selectByTags(array $tags)
    {
        if (count($tags) < 1) {
            throw new HrcException('You need to provide at least one tag.');
        }

        $tagFolder = str_replace($this->indexDir, '',
            rtrim($this->createFolderNameFromTags($tags), DIRECTORY_SEPARATOR));

        $result = glob($this->indexDir . '*' . $tagFolder . '*/*/*/*/index.db');
        if (!$result) {
            return false;
        }

        $keys = [];

        // open and read the index
        $ct = time();
        $entriesToDelete = [];
        foreach ($result as $index) {
            $indexHandler = fopen($index, 'r');
            while (($i = fread($indexHandler, 42))) {
                // validate timestamp
                $ttl = substr($i, 32, 10);
                if ($ttl > $ct) {
                    $keys[] = $i;
                } else {
                    $entriesToDelete[] = $i;
                }
            }
            // close the handler
            fclose($indexHandler);
        }

        // check if there are expired entries we need to delete
        foreach ($entriesToDelete as $i => $k) {
            $this->deleteEntryByKey(substr($k, 0, 32));
        }

        return $keys;
    }

    /**
     * Based on the provided key and tag list, a path to the index is created and returned.
     *
     * @param string $key
     * @param string $tags
     *
     * @return string
     */
    private function getIndex($key, $tags)
    {
        // tags folder
        $tagFolder = $this->createFolderNameFromTags($tags);

        if (!is_dir($tagFolder)) {
            mkdir($tagFolder, 0755, true);
        }

        // key folder
        $folder = $tagFolder . substr($key, 0, 2) . DIRECTORY_SEPARATOR . substr($key, 2,
                2) . DIRECTORY_SEPARATOR . substr($key, 4, 2) . DIRECTORY_SEPARATOR;
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        // check if index exists
        return $folder . 'index.db';
    }

    /**
     * Based on the provided list of tags, a path to the root tags folder is created.
     *
     * @param array $tags
     *
     * @return string
     */
    private function createFolderNameFromTags(array $tags)
    {
        natsort($tags);
        $tagFolder = '_';
        foreach ($tags as $t) {
            $tagFolder .= $t . '_';
        }

        return $this->indexDir . $tagFolder . DIRECTORY_SEPARATOR;
    }
}