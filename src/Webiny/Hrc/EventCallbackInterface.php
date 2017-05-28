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
}
