<?php
/**
 * Laravel Propel integration
 *
 * @author    Maxim Soloviev<BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;

class MigrationMigrateCommand extends \Propel\Generator\Command\MigrationMigrateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('propel:migration:migrate')
            ->setAliases([])
        ;
    }
}
