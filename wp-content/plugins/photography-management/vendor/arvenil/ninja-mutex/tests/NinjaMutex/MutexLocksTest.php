<?php
namespace NinjaMutex;

use \NinjaMutex\Lock\LockAbstract;
use \NinjaMutex\Lock\LockInterface;

/**
 * Tests for Mutex's Locks
 *
 *
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexLocksTest extends AbstractTest
{
    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAcquireAndReleaseLock($lockImplementor)
    {
        $mutex = new Mutex('forfiter', $lockImplementor);

        $this->assertTrue($mutex->acquireLock(0));
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());

        $this->assertTrue($mutex->releaseLock());
        $this->assertFalse($mutex->isAcquired());
        $this->assertFalse($mutex->isLocked());
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAllowToAcquireSelfOwnedLock($lockImplementor)
    {
        $mutex = new Mutex('forfiter', $lockImplementor);
        $mutex->acquireLock(0);

        // Another try to acquire lock is successful
        // because lock is already acquired by this mutex
        $this->assertTrue($mutex->acquireLock(0));
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testMultipleSelfAcquiredLocksRequiresMultipleReleasesToCompletelyReleaseMutex(
$lockImplementor
    ) {
        $mutex = new Mutex('forfiter', $lockImplementor);
        $mutex->acquireLock(0); // #1
        $mutex->acquireLock(0); // #2
        $mutex->acquireLock(0); // #3
        $this->assertTrue($mutex->releaseLock()); // #2
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());
        $this->assertTrue($mutex->releaseLock()); // #1
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());
        $this->assertTrue($mutex->releaseLock()); // #0
        $this->assertFalse($mutex->isAcquired());
        $this->assertFalse($mutex->isLocked());
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testUnableToAcquireLockHeldByOtherLock($lockImplementor)
    {
        $mutex1 = new Mutex('forfiter', $lockImplementor);
        $mutex1->acquireLock(0);

        $mutex = new Mutex('forfiter', $lockImplementor);

        // We don't acquire lock
        $this->assertFalse($mutex->isAcquired());

        // But it's held by other process
        $this->assertTrue($mutex->isLocked());

        // So we should be unable to acquire lock
        $this->assertFalse($mutex->acquireLock(0));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testUnableToReleaseLockHeldByOtherLock($lockImplementor)
    {
        $mutex1 = new Mutex('forfiter', $lockImplementor);
        $mutex1->acquireLock(0);

        $mutex = new Mutex('forfiter', $lockImplementor);

        // We don't acquire lock
        $this->assertFalse($mutex->isAcquired());

        // But it's held by other process
        $this->assertTrue($mutex->isLocked());

        // So we should be unable to release lock
        $this->assertFalse($mutex->releaseLock());
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAcquireLockTimeout($lockImplementor)
    {
        $mutex1 = new Mutex('forfiter', $lockImplementor);
        $mutex1->acquireLock(0);

        $mutex = new Mutex('forfiter', $lockImplementor);
        $sleep = LockAbstract::USLEEP_TIME;

        $time = microtime(true) * 1000;
        $mutex->acquireLock($sleep);
        $this->assertLessThanOrEqual(microtime(true) * 1000, $time + $sleep);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAcquireLockWithTimeoutImmiedietly($lockImplementor)
    {
        $mutex = new Mutex('forfiter', $lockImplementor);
        $sleep = LockAbstract::USLEEP_TIME;

        $time = microtime(true) * 1000;
        $mutex->acquireLock($sleep);
        $this->assertGreaterThan(microtime(true) * 1000, $time + $sleep);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAcquireAndReleaseSecondMutexWithoutReleaseTheFirstMutex($lockImplementor)
    {
        $firstMutex = new Mutex('forfiter', $lockImplementor);
        $firstMutex->acquireLock(0);

        $secondMutex = new Mutex('gieraryhir', $lockImplementor);
        $this->assertTrue($secondMutex->acquireLock(0));
        $this->assertTrue($secondMutex->isAcquired());
        $this->assertTrue($secondMutex->isLocked());
        $this->assertTrue($firstMutex->isAcquired());
        $this->assertTrue($firstMutex->isLocked());
        $this->assertTrue($secondMutex->releaseLock());
        $this->assertTrue($firstMutex->isAcquired());
        $this->assertTrue($firstMutex->isLocked());
    }

    /**
     * @issue https://github.com/arvenil/ninja-mutex/pull/1
     *
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfMutexIsReusableAfterSeveralAcquireReleaseCycles($lockImplementor)
    {
        $firstMutex = new Mutex('forfiter', $lockImplementor);
        $firstMutex->acquireLock();
        $firstMutex->releaseLock();
        $firstMutex->acquireLock();
        $firstMutex->releaseLock();

        $secondMutex = new Mutex('forfiter', $lockImplementor);
        $this->assertTrue($secondMutex->acquireLock());

        // cleanup
        $secondMutex->releaseLock();
    }
}
