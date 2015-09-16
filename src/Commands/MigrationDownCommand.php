<?php
/**
 * Laravel Propel integration
 *
 * @author    Maxim Soloviev<BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;

class MigrationDownCommand extends \Propel\Generator\Command\MigrationDownCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('propel:migration:down')
            ->setAliases([])
        ;
    }
}
