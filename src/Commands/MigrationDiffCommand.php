<?php

/**
 * Laravel Propel integration.
 *
 * @author    Maxim Soloviev<BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Commands;

class MigrationDiffCommand extends \Propel\Generator\Command\MigrationDiffCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('propel:migration:diff')
            ->setAliases([]);
    }
}
