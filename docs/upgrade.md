Upgrade guide
==============

Upgrade v1.2 or older up to v2.0
-----

2.0 is the first version which brakes BC and therefore we created that file :)
Main changes related to event of migration that package under naming and repo of
Propel community. Internally changed all NS'es at it cause requirement of some
renaming in your code.

Composer.json
--------

Package was renamed too and changed repo. You must replace:

    "scif/propel-laravel": "dev-master",

with:

    "propel/propel-laravel": "dev-master",

Service providers
--------

Now package use only one service provider `PropelIntegrationServiceProvider`
and you must open your `config/app.php` file and replace:

    Allboo\PropelLaravel\GeneratorServiceProvider::class,
    Allboo\PropelLaravel\RuntimeServiceProvider::class,

with:

    Propel\PropelLaravel\PropelIntegrationServiceProvider::class,

Command changes:
--------

  * `propel:create-schema` dropped and replaced with `propel:laravel:init`
  * `propel:convert-conf` renamed to `propel:config:convert`