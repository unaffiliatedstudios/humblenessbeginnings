<?php
namespace NinjaMutex\Lock;

/**
 * Lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockInterface
{

    /**
     * @param  string   $name
     * @param  null|int $timeout
     * @return bool
     */
    public function acquireLock($name, $timeout = null);

    /**
     * @param  string $name
     * @return bool
     */
    public function releaseLock($name);

    /**
     * @param  string $name
     * @return bool
     */
    public function isLocked($name);
}
