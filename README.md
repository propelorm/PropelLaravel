propel-laravel
==============

Propel2 integration for Laravel framework. Only 5.x versions supported. 4.x version can be found in repo of [initial developer](https://github.com/allBoo/propel-laravel) of current package.

Usage
-----

Require this package with composer using the following command:

    composer require scif/propel-laravel

After updating composer, add the ServiceProviders to the providers array in `app/config/app.php`

    'Allboo\PropelLaravel\GeneratorServiceProvider',
    'Allboo\PropelLaravel\RuntimeServiceProvider',

Next step is copy example config to your `app/config` directory.

    php artisan vendor:publish --provider 'Allboo\PropelLaravel\RuntimeServiceProvider'

Within provided config schemas files are located into `database/` folder, models are generated into `app/models`, migrations into `app/database/migrations`

You can now use Propel commands via artisan, ex

    php artisan propel:model:build

etc.

**Small hint**: you can define namespace of all generated models in schema just as attribute of database:

    <database … namespace="MyApp\Models">

Auth
--------

Package contains Auth driver binding which allows to store user info and fetch (`Auth::getUser()`) current logged in user as propel model. You need to change two settings in `config/auth.php`:

    'driver' => 'propel', // custom auth provider implemented in current package
    …
    'model' => 'MyApp\Models\User', // classname of user entity

Static Configuration
-------------

By default it builds configuration from main config `app/config/propel.php` in runtime but you may build static config `app/propel/config.php` by running

    propel:convert-conf


Services
--------

No service is provided.

Propel configures and manages itself by using static methods and its own service container, so no service is registered into Application.
Actually, the `GeneratorServiceProvider` class injects Propel tasks into artisan tasks list with prefix `propel:`
`RuntimeServiceProvider` class initializes Propel runtime configuration


See also
--------
[Make Propel models work with Laravel Form::model() without making it an array](https://github.com/stephangroen/propel-laravel)

Authors
--------

[First version written by Alex Kazynsky](https://github.com/allBoo). Now maintained by [Alexander Zhuralvev](https://github.com/SCIF). Thanks a lot to each [author](https://github.com/SCIF/propel-laravel/graphs/contributors)! Any bug reports and pull requests are appreciated!