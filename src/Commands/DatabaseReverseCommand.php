<?php

/**
 * Laravel Propel integration
 *
 * @author    Alexander Zhuravlev <scif-1986@ya.ru>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;

class DatabaseReverseCommand extends \Propel\Generator\Command\DatabaseReverseCommand
{
    protected function configure()
    {
        parent::configure();
        $this->getDefinition()->getOption('output-dir')->setDefault(app('config')->get('propel.propel.paths.schemaDir'));
        $this->getDefinition()->getArgument('connection')->setDefault(app('config')->get('propel.propel.reverse.connection'));
    }
}
