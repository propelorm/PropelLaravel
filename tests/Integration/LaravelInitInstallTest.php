<?php

use Propel\PropelLaravel\Commands\LaravelInit;

class LaravelInitInstallTest extends CommandTestCase
{

    public function testInit()
    {
        $this->executeCommand(new LaravelInit(), "\n\nyes\n\n");
    }
}
