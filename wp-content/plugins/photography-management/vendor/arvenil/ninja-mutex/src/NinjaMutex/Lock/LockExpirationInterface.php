<?php
namespace NinjaMutex\Lock;

/**
 * Lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockExpirationInterface
{
    /**
     * @param int $expiration Expiration time of the lock in seconds.
     */
    public function setExpiration($expiration);

    /**
     * @param  string $name
     * @return bool
     */
    public function clearLock($name);
}
