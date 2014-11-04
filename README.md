propel-laravel
==============

Propel2 integration for Laravel framework 4

Usage
-----

Require this package with composer using the following command:

    composer require allboo/propel-laravel

After updating composer, add the ServiceProviders to the providers array in app/config/app.php

    'Allboo\PropelLaravel\GeneratorServiceProvider',
    'Allboo\PropelLaravel\RuntimeServiceProvider',

Create Propel configuration file `app/config/propel.php`
Note: See example config in `example/config/propel.php`
Within provided config schemas files are located into `app/database/` folder, models are generated into `app/models`, migrations into `app/database/migrations`


You can now use Propel commands via artisan, ex

    php artisan propel:build

etc.


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
