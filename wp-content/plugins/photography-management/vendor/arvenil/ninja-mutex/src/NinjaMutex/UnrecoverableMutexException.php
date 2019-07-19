<?php
namespace NinjaMutex;

/**
 * Unrecoverable Mutex exception
 *
 * You shouldn't try to catch it unless you really know what are you doing
 * This kind of exception suggest you messed up your code and you should fix it
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class UnrecoverableMutexException extends MutexException
{
}
