<?php
namespace NinjaMutex;

use \NinjaMutex\Lock\LockInterface;

/**
 * Tests for MutexFabric
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexFabricTest extends AbstractTest
{
    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfInjectedImplementorIsSetAsDefault($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfInjectedImplementorDefaultImplementorIsNotOverwritten($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testRegisterNewImplementorAndSetIsAsDefault($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $mutexFabric->setDefaultLockImplementorName(get_class($lockImplementor) . '_forfiter');
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor) . '_forfiter');
    }

    /**
     * @dataProvider lockImplementorProvider
     * @expectedException \NinjaMutex\MutexException
     * @param LockInterface $lockImplementor
     */
    public function testThrowExceptionOnDuplicateImplementorName($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor), $lockImplementor);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testMutexCreationWithDefaultImplementor($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $this->assertInstanceOf('NinjaMutex\Mutex', $mutexFabric->get('lock'));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testMutexCreationWithSecondaryImplementor($lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $this->assertInstanceOf(
            'NinjaMutex\Mutex',
            $mutexFabric->get('lock', get_class($lockImplementor) . '_forfiter')
        );
    }
}
