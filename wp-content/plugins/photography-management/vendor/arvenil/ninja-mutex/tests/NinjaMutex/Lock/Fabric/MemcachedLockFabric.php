<?php
namespace NinjaMutex\Lock\Fabric;

use \Memcached;
use \NinjaMutex\Lock\MemcachedLock;

class MemcachedLockFabric implements LockFabricWithExpirationInterface {
    /**
     * @return MemcachedLock
     */
    public function create() {
        $memcached = new Memcached();
        $memcached->addServer('127.0.0.1', 11211);

        return new MemcachedLock($memcached);
    }
}
