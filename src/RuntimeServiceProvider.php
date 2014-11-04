<?php
/**
 * Laravel Propel integration
 *
 * @author    Alex Kazinskiy <alboo@list.ru>
 * @copyright 2014 Alex Kazinskiy
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/alboo/laravel-propel
 */

namespace Allboo\PropelLaravel;

use Illuminate\Support\ServiceProvider;
use Propel\Runtime\Propel;

class RuntimeServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->config['propel.propel.runtime.connections']) {
            throw new \InvalidArgumentException('Unable to guess Propel runtime config file. Please, initialize the "propel.runtime" parameter.');
        }

        /** @var \Propel\Runtime\ServiceContainer\StandardServiceContainer */
        $serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
        $serviceContainer->closeConnections();
        $serviceContainer->checkVersion('2.0.0-dev');

        $propel_conf = $this->app->config['propel.propel'];
        foreach ($propel_conf['runtime']['connections'] as $connection_name) {
            $config = $propel_conf['database']['connections'][$connection_name];
            if (!isset($config['classname'])) {
                $config['classname'] = '\\Propel\\Runtime\\Connection\\ConnectionWrapper';
            }

            $serviceContainer->setAdapterClass($connection_name, $config['adapter']);
            $manager = new \Propel\Runtime\Connection\ConnectionManagerSingle();
            $manager->setConfiguration($config);
            $manager->setName($connection_name);
            $serviceContainer->setConnectionManager($connection_name, $manager);
        }

        $serviceContainer->setDefaultDatasource($propel_conf['runtime']['defaultConnection']);

        Propel::setServiceContainer($serviceContainer);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (!class_exists('Propel\\Runtime\\Propel', true)) {
            throw new \InvalidArgumentException('Unable to find Propel, did you install it?');
        }
    }
}
