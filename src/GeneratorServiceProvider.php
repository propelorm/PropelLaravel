<?php
/**
 * Laravel Propel integration
 *
 * @author    Alex Kazinskiy <alboo@list.ru>
 * @author    Alexander Zhuravlev <scif-1986@ya.ru>
 * @author    Maxim Soloviev <BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

class GeneratorServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $finder = new Finder();
        $finder->files()->name('*.php')->in(__DIR__.'/../../../propel/propel/src/Propel/Generator/Command')->depth(0);

        $commands = [];
        foreach ($finder as $file) {
            $ns = '\\Propel\\Generator\\Command';
            $r  = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                $c = $r->newInstance();

                $command = 'command.propel.' . $c->getName();
                $commands[] = $command;

                $c->setName('propel:' . $c->getName());
                $c->setAliases([]);

                $this->app[$command] = $this->app->share(
                    function ($app) use ($c) {
                        return $c;
                    }
                );
            }
        }

        $commands[] = Commands\CreateSchema::class;

        $this->commands($commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('command.propel');
    }

}
