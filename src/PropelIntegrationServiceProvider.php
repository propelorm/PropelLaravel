<?php

/**
 * Laravel Propel integration.
 *
 * @author    Alex Kazinskiy <alboo@list.ru>
 * @author    Alexander Zhuravlev <scif-1986@ya.ru>
 * @author    Maxim Soloviev <BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Propel\Common\Config\Exception\InvalidConfigurationException;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Propel;
use Symfony\Component\Console\Input\ArgvInput;

class PropelIntegrationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/propel.php', 'propel'
        );
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        if (\App::runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config' => config_path(),
            ], 'propel-laravel');

            $command = (new ArgvInput())->getFirstArgument();

            if ('propel:' === substr($command, 0, 7) && !is_file(base_path('config/propel.php'))) {
                // config_path('propel.php') will work too untill config dir in app root
                throw new PropelException('You MUST run "php ./artisan vendor:publish --provider '.__CLASS__.'" before use propel console commands ');
            }
        }

        $configurator = $this->app->make('config');

        $converted_conf_file = $configurator->get('propel.propel.paths.phpConfDir').'/config.php';

        // load pregenerated config
        if (file_exists($converted_conf_file)) {
            include $converted_conf_file;
        } else {
            $this->registerRuntimeConfiguration();
        }

        if (version_compare($this->app->version(), '5.2', '<')) {
            if ('propel' === $configurator->get('auth.driver')) {
                $this->registerPropelAuth($configurator->get('auth.model'));
            }
        } else {
            foreach ($configurator->get('auth.providers') as $provider) {
                if ('propel' === $provider['driver']) {
                    $this->registerPropelAuth($provider['model']);
                    break;
                }
            }
        }

        if (\App::runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register propel runtime configuration.
     *
     * @return void
     */
    protected function registerRuntimeConfiguration()
    {
        $propel_conf = $this->app->config['propel.propel'];

        if (!isset($propel_conf['runtime']['connections'])) {
            throw new \InvalidArgumentException('Unable to guess Propel runtime config file. Please, initialize the "propel.runtime" parameter.');
        }

        /** @var $serviceContainer \Propel\Runtime\ServiceContainer\StandardServiceContainer */
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->closeConnections();
        $serviceContainer->checkVersion('2.0.0-dev');

        $runtime_conf = $propel_conf['runtime'];

        // set connections
        foreach ($runtime_conf['connections'] as $connection_name) {
            $config = $propel_conf['database']['connections'][$connection_name];

            $serviceContainer->setAdapterClass($connection_name, $config['adapter']);
            $manager = new ConnectionManagerSingle();
            $manager->setConfiguration($config + ['paths' => $propel_conf['paths']]);
            $manager->setName($connection_name);
            $serviceContainer->setConnectionManager($connection_name, $manager);
        }

        $serviceContainer->setDefaultDatasource($runtime_conf['defaultConnection']);

        // set loggers
        $has_default_logger = false;
        if (isset($runtime_conf['log'])) {
            $has_default_logger = array_key_exists('defaultLogger', $runtime_conf['log']);
            foreach ($runtime_conf['log'] as $logger_name => $logger_conf) {
                $serviceContainer->setLoggerConfiguration($logger_name, $logger_conf);
            }
        }

        if (!$has_default_logger) {
            $serviceContainer->setLogger('defaultLogger', \Log::getMonolog());
        }

        Propel::setServiceContainer($serviceContainer);
    }

    /**
     * Register propel auth provider.
     *
     * @param string $user_class
     *
     * @return void
     */
    protected function registerPropelAuth($user_class)
    {
        // skip auth driver adding if running as CLI to avoid auth model not found
        if (!class_exists($user_class, true)) {
            if (\App::runningInConsole()) {
                \Log::notice('Class ' . $user_class . ' which uses as class of authorized users is not available');
                return;
            }

            throw new LogicException(sprintf('Model "%s" marked as auth model but does not exists and can not be found', $user_class));;
        }

        $query = new $user_class;

        if (!$query instanceof ActiveRecordInterface) {
            throw new InvalidConfigurationException(sprintf('Model "%s" of propel auth provider is not a valid Propel ModelCriteria class', $user_class));
        }

        $query = $query->buildPkeyCriteria();
        $query->clear();

        $this->app['auth']->provider('propel', function (Application $app) use ($query) {
            return new Auth\PropelUserProvider($query, $app->make('hash'));
        });
    }

    public function registerCommands()
    {
        $commands = [
            Commands\ConfigConvertCommand::class,
            Commands\DatabaseReverseCommand::class,
            Commands\GraphvizGenerateCommand::class,
            Commands\MigrationDiffCommand::class,
            Commands\MigrationDownCommand::class,
            Commands\MigrationMigrateCommand::class,
            Commands\MigrationStatusCommand::class,
            Commands\MigrationUpCommand::class,
            Commands\ModelBuildCommand::class,
            Commands\SqlBuildCommand::class,
            Commands\SqlInsertCommand::class,
            Commands\LaravelInit::class,
        ];

        $this->commands($commands);
    }
}
