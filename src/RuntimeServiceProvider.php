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

use Allboo\PropelLaravel\Auth\PropelUserProvider;
use Illuminate\Support\ServiceProvider;
use Propel\Common\Config\Exception\InvalidConfigurationException;
use Propel\Runtime\ActiveQuery\Criteria;
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

        // load pregenerated config
        if (file_exists(app_path() . '/propel/config.php')) {
            Propel::init(app_path() . '/propel/config.php');
            return;
        }

        // runtime configuration
        /** @var \Propel\Runtime\ServiceContainer\StandardServiceContainer */
        $serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
        $serviceContainer->closeConnections();
        $serviceContainer->checkVersion('2.0.0-dev');

        $propel_conf = $this->app->config['propel.propel'];
        $runtime_conf = $propel_conf['runtime'];

        // set connections
        foreach ($runtime_conf['connections'] as $connection_name) {
            $config = $propel_conf['database']['connections'][$connection_name];
            if (!isset($config['classname'])) {
                if ($this->app->config['app.debug']) {
                    $config['classname'] = '\\Propel\\Runtime\\Connection\\DebugPDO';
                }
                else {
                    $config['classname'] = '\\Propel\\Runtime\\Connection\\ConnectionWrapper';
                }
            }

            $serviceContainer->setAdapterClass($connection_name, $config['adapter']);
            $manager = new \Propel\Runtime\Connection\ConnectionManagerSingle();
            $manager->setConfiguration($config + [$propel_conf['paths']]);
            $manager->setName($connection_name);
            $serviceContainer->setConnectionManager($connection_name, $manager);
        }

        $serviceContainer->setDefaultDatasource($runtime_conf['defaultConnection']);

        // set loggers
        $has_default_logger = false;
        if (isset($runtime_conf['log'])) {
            foreach ($runtime_conf['log'] as $logger_name => $logger_conf) {
                $serviceContainer->setLoggerConfiguration($logger_name, $logger_conf);
                $has_default_logger |= $logger_name === 'defaultLogger';
            }
        }
        if (!$has_default_logger) {
            $serviceContainer->setLogger('defaultLogger', \Log::getMonolog());
        }

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

        if ('propel' == \Config::get('auth.driver')) {
            $query_name = \Config::get('auth.user_query', false);

            if ($query_name) {
                $query = new $query_name;
                if ( ! $query instanceof Criteria) {
                    throw new InvalidConfigurationException("Configuration directive «auth.user_query» must contain valid classpath of user Query. Excpected type: instanceof Propel\\Runtime\\ActiveQuery\\Criteria");
                }
            } else {
                $user_class = \Config::get('auth.model');

                $query = new $user_class;

                if ( ! method_exists($query, 'buildCriteria')) {
                    throw new InvalidConfigurationException("Configuration directive «auth.model» must contain valid classpath of model, which has method «buildCriteria()»");
                }

                $query = $query->buildPkeyCriteria();
                $query->clear();
            }

            \Auth::extend('propel', function(\Illuminate\Foundation\Application $app) use ($query)
            {
                return new PropelUserProvider($query, $app->make('hash'));
            });
        }
    }
}
