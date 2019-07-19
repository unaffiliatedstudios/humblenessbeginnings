<?php
namespace NinjaMutex;

use \NinjaMutex\Mock\MockLock;

/**
 * Tests for Mutex
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @issue https://github.com/arvenil/ninja-mutex/pull/4
     */
    public function testIfMutexDestructorThrowsWhenBackendIsUnavailable()
    {
        $lockImplementor = new MockLock();
        $mutex = new Mutex('forfiter', $lockImplementor);

        $this->assertFalse($mutex->isAcquired());
        $this->assertTrue($mutex->acquireLock());
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->acquireLock());
        $this->assertTrue($mutex->isAcquired());

        // make backend unavailable
        $lockImplementor->setAvailable(false);

        try {
            // explicit __destructor() call, should throw UnrecoverableMutexException
            $mutex->__destruct();
        } catch (UnrecoverableMutexException $e) {
            // make backend available again
            $lockImplementor->setAvailable(true);
            // release lock
            $this->assertTrue($mutex->releaseLock());
            $this->assertFalse($mutex->releaseLock());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
