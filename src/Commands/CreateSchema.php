<?php
/**
 * Laravel Propel integration
 *
 * @author    Soloviev Maxim AKA Big_Shark <BigShark666@gmail.com>
 * @copyright 2015 Soloviev Maxim
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/SCIF/laravel-propel
 */

namespace Allboo\PropelLaravel\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Inspiring;

class CreateSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'propel:create-schema {--sample}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create schema';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Config $config)
    {
        $sampleOption = $this->option('sample');

        $type = 'empty';
        if( $sampleOption )
        {
            $type = 'sample';
        }
        $from = 'vendor/scif/propel-laravel/resources/'.$type.'_schema.xml';
        $to = $config->get('propel.propel.paths.schemaDir') . '/' . $config->get('propel.propel.generator.schema.basename') . '.xml';

        if( ! copy($from, $to) )
        {
            throw \Exception('Failed to copy a file "' . $from . '" to "' . $to . '"');
        }

        $content = file_get_contents($to);

        $name = $config->get('propel.propel.generator.defaultConnection');
        $content = str_replace('**NAME**', $name, $content);

        $nameSpace = $this->laravel->getNamespace() . str_replace(app_path().DIRECTORY_SEPARATOR, '', $config->get('propel.propel.paths.phpDir'));
        $content = str_replace('**NAMESPACE**', $nameSpace, $content);

        file_put_contents($to, $content);

        $this->comment(PHP_EOL . 'Create ' . $to . PHP_EOL);
    }
}
