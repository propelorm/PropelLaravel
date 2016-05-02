<?php
/**
 * Laravel Propel integration
 *
 * @author    Maxim Soloviev <BigShark666@gmail.com>
 * @author    Alexander Zhuravlev <scif-1986@ya.ru>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Propel\Generator\Command\Helper\ConsoleHelper3;
use Propel\Generator\Command\Helper\ConsoleHelperInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Propel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LaravelInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'propel:laravel:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample or empty schema';

    /** @var Config $config */
    protected $config;

    /** @var string $ns */
    protected $ns;

    /** @var bool $have_schema */
    protected $have_schema = false;

    protected function configure()
    {
        parent::configure();
        $this->setName('propel:laravel:init');
        $this->config = app('config');
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper  = $this->createConsoleHelper($input, $output);

        $helper->writeSection('<comment>Laravel5 Propel integration initializer</comment>');
        $helper->writeln('');

        $schema_path = $this->getTargetSchemaPath();

        if (is_file($schema_path)) {
            $helper->writeBlock('You already have schema.xml file. Skip schema.xml creation. If you want to use Auth
             adapter provided this package — you should visit wiki and made changes manual');

            return;
        }

        $this->fetchModelNS($helper);
        $this->processSchemaCreation($helper);

        $auth = $helper->askConfirmation('Do you want to use Propel User provider for Auth?', true);

        if ($auth) {
            $this->installAuthProvider($helper);
        }

        $helper->writeBlock('Thank you for your decision to test Propel!', 'info');
    }

    /**
     * @param string         $schema_from Prefix of schema provided by that package ("empty" or "sample is valid value)
     * @param string|null    $schema_to_prefix Name without suffix (which is typically `schema`). Optional
     * @param ConsoleHelperInterface $helper
     *
     * @throws PropelException Failed to copy a schema file
     */
    protected function copySchema($schema_from, $schema_to_prefix = null, ConsoleHelperInterface $helper)
    {
        $from = dirname(__DIR__ . '/../../resources/.') . DIRECTORY_SEPARATOR . $schema_from . '_schema.xml';
        $to   = $this->getTargetSchemaPath($schema_to_prefix);

        $this->copySchemaFile($from, $to, $helper);
    }

    /**
     * @param string         $from
     * @param string         $to
     * @param ConsoleHelperInterface $helper
     *
     * @throws PropelException Failed to copy a schema file
     */
    protected function copySchemaFile($from, $to, ConsoleHelperInterface $helper)
    {
        $database_name  = $this->config->get('propel.propel.generator.defaultConnection');
        $nameSpace      = $this->fetchModelNS($helper);

        if (! copy($from, $to)) {
            throw new PropelException(sprintf('Failed to copy a schema file "%s" to "%s"', $from, $to));
        }

        $content = str_replace(
            ['**NAMESPACE**', '**NAME**'],
            [$nameSpace, $database_name],
            file_get_contents($to)
        );

        file_put_contents($to, $content);

        $this->addDtdLinkToSchema($to);
        $helper->writeSection('Created ' . $to . "\n");
    }

    /**
     * @param string $schema_path Path to schema.xml file
     */
    protected function addDtdLinkToSchema($schema_path)
    {
        $schema_dir = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $this->config->get('propel.propel.paths.phpDir'));
        $root       = implode(DIRECTORY_SEPARATOR, array_fill(0, substr_count($schema_dir, DIRECTORY_SEPARATOR), '..'));
        $head       = sprintf("?>\n<!DOCTYPE database SYSTEM \"%s/vendor/propel/propel/resources/dtd/database.dtd\">\n", $root);
        $schema     = \file_get_contents($schema_path);
        \file_put_contents($schema_path, str_replace('?>', $head, $schema));
    }

    protected function processSchemaCreation(ConsoleHelperInterface $helper)
    {
        $this->have_schema = $helper->askConfirmation('Do you have existing schema.xml and will use it?', false);

        if ($this->have_schema) {
            return;
        }

        $empty_db = empty(Propel::getConnection()->query('SHOW TABLES;')->fetch());
        $reverse = false;

        if ($empty_db) {
            $helper->writeBlock('You don\'t have tables in database. Skip reverse database question');
            $helper->writeln('');
        } else {
            $reverse = $helper->askConfirmation('Do you want to reverse your current database structure?', true);
        }

        if ($reverse) {
            $this->reverseDb($helper);
            return;
        }

        $sample = $helper->askConfirmation('Do you want to create sample schema.xml (bookstore)?', false);

        if ($sample) {
            $this->copySchema('sample', '', $helper);
        } else {
            $empty = $helper->askConfirmation('Do you want to create empty schema.xml?', true);
            $empty && $this->copySchema('empty', '', $helper);
        }
    }

    protected function installAuthProvider(ConsoleHelperInterface $helper)
    {
        $modelname = $this->fetchModelNS($helper) . '\User';

        if (is_file($this->getTargetSchemaPath('auth_'))) {
            $helper->writeBlock('You are already have auth_schema.xml. Installation of Auth provider cancelled', 'error');

            return;
        }

        if ($this->have_schema) {
            $this->copySchema('auth', 'auth_', $helper);
        }

        $this->call('propel:model:build');

        $helper->writeln('Models for schemas were built successful');

        $this->processConfig($modelname);
        $this->copyUserModel($modelname, $helper);
    }

    /**
     * @param string $modelname
     *
     * @throws PropelException
     */
    protected function processConfig($modelname) {
        $auth_file     = config_path('auth.php');
        $auth_config   = \file_get_contents($auth_file);
        $supported_old = 'Supported: "database", "eloquent"';
        $supported_new = 'Supported: "database", "eloquent", "propel"';

        if (false !== strpos($auth_config, $supported_old)) {
            $auth_config = str_replace($supported_old, $supported_new, $auth_config);
        }

        if (version_compare($this->laravel->version(), '5.2', '>')) {
            $driver_pattern = '/(providers[^\[]+\[[^\[]+users[^\[]+\[\s+[\'"]driver[\'"]\s*=>)[^,]+,/';
        } else {
            $driver_pattern = '/([\'"]driver[\'"]\s*=>)[^,]+,/';
        }

        $driver_new     = "\\1 'propel',";
        $model_pattern  = '/[\'"]model[\'"]\s*=>[^,]+,/';
        $model_new      = sprintf('\'model\' => %s::class,', $modelname);

        $auth_new_config = preg_replace(
            [ $driver_pattern, $model_pattern],
            [ $driver_new, $model_new ],
            $auth_config
        );

        if ($auth_new_config === $auth_config) {
            throw new PropelException('Seems like your already use propel provider. Nothing to change');
        }

        if (false === $auth_new_config) {
            throw new PropelException('Auth config did not processed');
        }

        \file_put_contents($auth_file, $auth_new_config);
    }

    /**
     * @param string                 $model_name
     * @param ConsoleHelperInterface $helper
     *
     * @throws PropelException
     */
    protected function copyUserModel($model_name, ConsoleHelperInterface $helper)
    {
        $model_path = trim(
                        str_replace($this->laravel->getNamespace(), '', $model_name),
                        '\\'
                       );
        $model_path = str_replace('\\', DIRECTORY_SEPARATOR, $model_path);
        $to         = app_path($model_path . '.php');

        if (is_file($to)) {
            $helper->writeBlock('You have User model already. Read docs to adopt your model at our wiki');
            return;
        }

        $from = dirname(__DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../../resources/.')) . DIRECTORY_SEPARATOR . 'User.php';
        $content = \file_get_contents($from);

        $model_ns = substr($model_name, 0, strrpos($model_name, '\\'));
        $content = str_replace('**NAMESPACE**', $model_ns, $content);
        $saved = \file_put_contents($to, $content);

        if (!$saved) {
            throw new PropelException('Can not write User Model to destination file: ' . $to);
        }
    }

    /**
     * @return string
     */
    protected function proposeNS()
    {
        return $this->laravel->getNamespace()
                    . str_replace(
                        app_path() . DIRECTORY_SEPARATOR,
                        '',
                        $this->config->get('propel.propel.paths.phpDir')
                    );
    }

    /**
     * @param ConsoleHelperInterface $helper
     *
     * @return string Namespace of models. Without trailing slash
     */
    protected function fetchModelNS(ConsoleHelperInterface $helper)
    {
        if (null === $this->ns) {
            $helper->writeBlock('Important! Beforehand you MUST set namespace by "./artisan app:name" command before', 'question');
            $this->ns = $helper->askQuestion(
                            'Is it your prefered namespace to store models?',
                            $this->proposeNS()
            );
        }

        return $this->ns;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ConsoleHelper3
     */
    protected function createConsoleHelper(InputInterface $input, OutputInterface $output)
    {
        $helper = new ConsoleHelper3($input, $output);

        /* Console input testing magic: we passthru hinted input to custom Propel helper */
        $inputStream = $this->getHelper('question')->getInputStream();

        is_resource($inputStream) && $helper->setInputStream($inputStream);

        $this->getHelperSet()->set($helper);

        return $helper;
    }

    /**
     * @param ConsoleHelperInterface $helper
     *
     * @throws PropelException
     */
    protected function reverseDb(ConsoleHelperInterface $helper)
    {
        $reversed = $this->call('propel:database:reverse', [
                         'connection' => $this->config->get('propel.propel.reverse.connection'),
                     ]);

        if (0 !== $reversed) {
            throw new PropelException('Database reverse command executed not successfully');
        }

        $file = $this->getTargetSchemaPath();

        if (!is_file($file)) {
            throw  new PropelException(sprintf('Schema.xml was not found in expected path (%s)', $file));
        }

        $new = sprintf('<database namespace="%s"', $this->fetchModelNS($helper));
        $new_content = str_replace('<database', $new, \file_get_contents($file));

        \file_put_contents($file, $new_content);

        $this->addDtdLinkToSchema($file);
    }

    /**
     * @param  string $prefix
     *
     * @return string Full destination path of schema file
     */
    protected function getTargetSchemaPath($prefix = null)
    {
        return $this->config
                    ->get('propel.propel.paths.schemaDir')
              . DIRECTORY_SEPARATOR
              . $prefix
              . $this->config
                     ->get('propel.propel.generator.schema.basename')
              . '.xml';
    }
}
