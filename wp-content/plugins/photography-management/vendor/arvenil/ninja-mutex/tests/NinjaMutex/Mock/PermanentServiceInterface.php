<?php
namespace NinjaMutex\Mock;

/**
 * Backend interface
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface PermanentServiceInterface
{

    /**
     * @param bool $available
     */
    public function setAvailable($available);
}
