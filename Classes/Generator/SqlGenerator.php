<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class SqlGenerator {

    /**
     * returns sql statements of all elements
     */
    protected function getSqlByConfiguration(): string
    {

        return "CREATE TABLE test (
            parentid int(11) DEFAULT '0' NOT NULL,
            parenttable varchar(255) DEFAULT '',
        );\nCREATE TABLE test2 (
            parentid int(11) DEFAULT '0' NOT NULL,
            parenttable varchar(255) DEFAULT '',
        );";
    }

    /**
     * Adds the SQL for all elements to the psr-14 AlterTableDefinitionStatementsEvent event.
     *
     * @param AlterTableDefinitionStatementsEvent $event
     */
    public function addDatabaseTablesDefinition(AlterTableDefinitionStatementsEvent $event): void
    {
        $event->addSqlData($this->getSqlByConfiguration());
    }
}
