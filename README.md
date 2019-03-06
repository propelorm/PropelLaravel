propel-laravel
==============

Propel2 integration for Laravel framework. Only 5.x versions supported.
4.x version can be found in repo of [initial developer](https://github.com/allBoo/propel-laravel) of current package.

Usage
-----

First of all: you need to understand that current package is in heavy development.
We try to maintain 1.* branch as stable and tested as it used by 1M of angry developers, which have guns.
Propel2 seems pretty stable, but still in development and currently it require that your
installation must have minimum stability is `alpha`. Open your `composer.json`
and write after `config` section:

    "config": {
        "preferred-install": "dist"
    },
    "minimum-stability": "alpha"

Require this package with composer using the following command:

    composer require propel/propel-laravel

After updating composer, add the ServiceProviders to the providers array in `app/config/app.php`

    Propel\PropelLaravel\PropelIntegrationServiceProvider::class,

Next step is copy example config to your `app/config` directory.

    php ./artisan vendor:publish --provider 'Propel\PropelLaravel\RuntimeServiceProvider'

Within provided config: schemas files are located into `database/` folder,
models are generated into `app/models`, migrations into `app/database/migrations`

You can now use Propel commands via artisan, for example:

    php ./artisan propel:model:build

etc.

For new users of propel there is command creating sample `schema.xml` file:

    php ./artisan propel:schema:create

If you are trying Propel2 on existing database — you can use
[reverse database](http://propelorm.org/documentation/cookbook/working-with-existing-databases.html) command:

    php ./artisan propel:database:reverse mysql

Since version 2.0.0-alpha5 there is awesome config node `exclude_tables` in config,
which allows you to mix different project tables in one database.

**Small hint**: you can define namespace of all generated models in schema just as attribute of database:

    <database … namespace="MyApp\Models">

Auth
--------

Package contains Auth driver binding which allows to store user info and fetch (`Auth::getUser()`) current logged in user as propel model. You need to change two settings in `config/auth.php`:

    'driver' => 'propel', // custom auth provider implemented in current package
    …
    'model' => MyApp\Models\User::class, // classname of user entity

After schema creating and model generation you must enhance your model to implement all laravel Auth requirements. Generic user model seems so:

    use MyApp\Models\Base\User as BaseUser;
    use Illuminate\Auth\Authenticatable;
    use Illuminate\Auth\Passwords\CanResetPassword;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

    class User extends BaseUser implements AuthenticatableContract, CanResetPasswordContract
    {
        use Authenticatable, CanResetPassword;

        public function getAuthIdentifier()
        {
            return $this->id;
        }
    }

Static Configuration
-------------

By default it builds configuration from main config `app/config/propel.php` in runtime but you may build static config `app/propel/config.php` by running

    php ./artisan propel:config:convert


Services
--------

No service is provided.

Propel configures and manages itself by using static methods and its own service container, so no service is registered into Application.
Actually, the `GeneratorServiceProvider` class injects Propel tasks into artisan tasks list with prefix `propel:`
`RuntimeServiceProvider` class initializes Propel runtime configuration

Known issues
--------

* Cli command `propel:database:reverse` save reversed schema file to root of project
* There isn't schema file and command for initial user creation, but it's in our [roadmap](https://github.com/SCIF/propel-laravel/issues/4) and will arrive soon

Authors
--------

[First version written by Alex Kazynsky](https://github.com/allBoo).
Now maintained by [Alexander Zhuralvev](https://github.com/SCIF) and
[Maxim Soloviev](https://github.com/Big-Shark).
Thanks a lot to each [author](https://github.com/propelorm/PropelLaravel/graphs/contributors)! Any bug reports and pull requests are appreciated!


See also
--------

[Make Propel models work with Laravel Form::model() without making it an array](https://github.com/stephangroen/propel-laravel)
