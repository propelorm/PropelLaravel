<?php

use Propel\LaravelTest\Models\UserQuery;
use Propel\LaravelTest\Models\User;
use Propel\PropelLaravel\Commands\LaravelInit;
use Propel\PropelLaravel\Commands\MigrationDiffCommand;
use Propel\PropelLaravel\Commands\MigrationMigrateCommand;
use Propel\PropelLaravel\Commands\ModelBuildCommand;
use Illuminate\Console\Command;

class LaravelInitAuthTest extends TestCase
{
    public function testAuth()
    {
        $password = 'Nmk32bvdf';
        $email    = 'tester@test.tld';

        UserQuery::create('u')->findOneByEmail($email);
        $user = new User();
        $user->setName('test');
        $user->setEmail($email);
        $user->setPassword(app('hash')->make($password));
        $user->save();

        $user_check = UserQuery::create('u')->findOneByEmail($email);

		$this->assertTrue($user === $user_check);
		$this->assertFalse(\Auth::attempt(['email' => $email, 'password' => 123]));
        $this->assertNull(\Auth::getUser());
		$this->assertTrue(\Auth::attempt(['email' => $email, 'password' => $password]));
        Log::error(get_class(\Auth::getUser()));

        $this->assertInstanceOf(User::class, \Auth::getUser());
    }
}
