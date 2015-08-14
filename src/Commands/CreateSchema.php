<?php
/**
 * Laravel Propel integration
 *
 * @author    Maxim Soloviev<BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;

class CreateSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'propel:schema:create {--sample}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample or empty schema';

    /**
     * Execute the console command.
     *
     * @param Config $config
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle(Config $config)
    {
        $sampleOption = $this->option('sample');

        $type = 'empty';
        if( $sampleOption )
        {
            $type = 'sample';
        }

        $from = __DIR__ . '/../../resources/'.$type.'_schema.xml';
        $to = $config->get('propel.propel.paths.schemaDir') . '/' . $config->get('propel.propel.generator.schema.basename') . '.xml';

        if( ! copy($from, $to) )
        {
            throw new \Exception('Failed to copy a file "' . $from . '" to "' . $to . '"');
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
