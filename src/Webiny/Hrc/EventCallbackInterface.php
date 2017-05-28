<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc;

interface EventCallbackInterface
{
    /**
     * Executes after a cache rule was matched, but before the entry was saved.
     * Modifying the SavePayload will effect how the cache will be saved.
     *
     * @param SavePayload $payload
     *
     * @return void
     */
    public function beforeSave(SavePayload $payload);

    /**
     * Executes after a successful cache save.
     *
     * @param SavePayload $payload
     *
     * @return void
     */
    public function afterSave(SavePayload $payload);

    /**
     * Executes after a cache rule has matched, but before the check on the storage is performed for the given cache key.
     * You can use this callback to modify the cache key and to set the purge flag to purge the cache entry if one is found.
     * Note: the getContent method on payload will return null at this point.
     *
     * @param ReadPayload $payload
     *
     * @return void
     */
    public function beforeRead(ReadPayload $payload);

    /**
     * This callback is only performed when a storage returns the cached content.
     * At this point getContent will return the actual content.
     *
     * @param ReadPayload $payload
     *
     * @return void
     */
    public function afterRead(ReadPayload $payload);
}
