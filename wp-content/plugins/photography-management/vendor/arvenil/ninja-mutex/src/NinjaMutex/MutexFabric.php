<?php
namespace NinjaMutex;

use \NinjaMutex\Lock\LockInterface;

/**
 * Mutex fabric
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexFabric
{
    protected $defaultLockImplementorName;
    protected $implementors = array();
    protected $mutexes = array();

    /**
     * @param string $lockImplementorName
     * @param $lockImplementor
     */
    public function __construct($lockImplementorName, $lockImplementor)
    {
        $this->registerLockImplementor($lockImplementorName, $lockImplementor);
    }

    /**
     *
     * @param  string         $name
     * @param  LockInterface  $implementor
     * @throws MutexException
     */
    public function registerLockImplementor($name, $implementor)
    {
        if (isset($this->implementors[$name])) {
            throw new MutexException(sprintf('Name %s is already used', $name));
        }

        if (null === $this->defaultLockImplementorName) {
            $this->defaultLockImplementorName = $name;
        }

        $this->implementors[$name] = $implementor;
    }

    /**
     * @param string $registeredLockImplementorName
     */
    public function setDefaultLockImplementorName($registeredLockImplementorName)
    {
        $this->defaultLockImplementorName = $registeredLockImplementorName;
    }

    public function getDefaultLockImplementorName()
    {
        return $this->defaultLockImplementorName;
    }

    /**
     * Create and/or get mutex
     *
     * @param  string $name
     * @param  string $registeredLockImplementorName
     * @return Mutex
     */
    public function get($name, $registeredLockImplementorName = null)
    {
        if (null === $registeredLockImplementorName) {
            $registeredLockImplementorName = $this->getDefaultLockImplementorName();
        }

        if (!isset($this->mutexes[$registeredLockImplementorName][$name])) {
            $this->createMutex($name, $registeredLockImplementorName);
        }

        return $this->mutexes[$registeredLockImplementorName][$name];
    }

    /**
     * @param string $name
     * @param string $registeredLockImplementorName
     */
    protected function createMutex($name, $registeredLockImplementorName)
    {
        $this->mutexes[$registeredLockImplementorName][$name] = new Mutex($name, $this->implementors[$registeredLockImplementorName]);
    }
}
