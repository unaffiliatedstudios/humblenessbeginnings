<?php
namespace NinjaMutex\Lock\Fabric;

use \Memcache;
use \NinjaMutex\Lock\MemcacheLock;

class MemcacheLockFabric implements LockFabricWithExpirationInterface {
    /**
     * @return MemcacheLock
     */
    public function create() {
        $memcache = new Memcache();
        $memcache->connect('127.0.0.1', 11211);

        return new MemcacheLock($memcache);
    }
}
