<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\CacheStorage;

/**
 * Class FileSystem - cache storage that uses filesystem to store the cache.
 *
 * @package Webiny\Hrc\CacheStorage
 */
class FileSystem implements CacheStorageInterface
{
    /**
     * @var string Cache root folder.
     */
    private $cacheDir;

    /**
     * @var integer Remaining ttl of the current cache entry;
     */
    private $remainingTtl = 0;


    /**
     * @param string $cacheDir Absolute path to the cache root folder.
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR);
        if (!$this->cacheDir) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->cacheDir = realpath($this->cacheDir) . DIRECTORY_SEPARATOR;
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
        $cacheFile = $this->getCachePath($key);

        clearstatcache(true, $cacheFile);
        if (file_exists($cacheFile)) {
            $cache = file_get_contents($cacheFile);
            $cache = json_decode($cache, true);
            if ($cache['ttl'] > time()) {
                $this->remainingTtl = $cache['ttl'] - time();
                return $cache['content'];
            } else {
                $this->purge($key);
            }
        }

        return false;
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
        $cacheFile = $this->getCachePath($key);
        $cache = json_encode(['ttl' => (time() + $ttl), 'content' => $content]);

        file_put_contents($cacheFile, $cache);

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
        $cacheFile = $this->getCachePath($key);

        clearstatcache(true, $cacheFile);
        if (file_exists($cacheFile)) {
            unset($cacheFile);

            return true;
        }

        return false;
    }

    /**
     * Creates the cache folder hierarchy and returns the full path to the given cache file.
     *
     * @param string $cacheKey
     *
     * @return string
     */
    private function getCachePath($cacheKey)
    {
        $folder = $this->cacheDir . substr($cacheKey, 0, 2) . DIRECTORY_SEPARATOR . substr($cacheKey, 2,
                2) . DIRECTORY_SEPARATOR . substr($cacheKey, 4, 2) . DIRECTORY_SEPARATOR;
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        return $folder . $cacheKey;
    }

    /**
     * @return int Returns the remaining ttl of the matched cache rule.
     */
    public function getRemainingTtl()
    {
        return $this->remainingTtl;
    }
}