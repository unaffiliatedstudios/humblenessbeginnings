<?php
namespace NinjaMutex\Lock\Fabric;
use \NinjaMutex\Lock\LockInterface;
use \NinjaMutex\Lock\LockExpirationInterface;

/**
 * Lock Fabric interface
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockFabricWithExpirationInterface
{
    /**
     * @return LockInterface|LockExpirationInterface
     */
    public function create();
}
