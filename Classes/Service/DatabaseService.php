<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Contentblocks\ContentblocksRegApi\Constants;

/** Class DatabaseService
 * Manages database updates e. g. add columns after creating a new content block.
 */
class DatabaseService implements SingletonInterface
{
    /**
     * @var YamlToSqlTranslationService
     */
    protected $yamlTranslator;

    /**
     * @var array
    */
    protected $tt_contentFields;

    /**
     * @var array
    */
    protected $collectionFields;

    /**
     * @var string
    */
    protected $cType;

    public function __construct(YamlToSqlTranslationService $yamlTranslator)
    {
        $this->yamlTranslator = $yamlTranslator;
        $this->tt_contentFields = [];
        $this->collectionFields = [];
        $this->cType = '';
    }

    /** public function addColumnsToCType
     *
     * Adds a given array of fields to tt_content and tx_contentblocks_reg_api_collection.
     *
     * Returns false if:
     * - collection without a field inside (misconfiguration)
     * - fields array is empty
     * - CType is empty
     * - could not update database
     * - something went wrong while updating database
     *
     * @param array $fields
     * @param string $ctype
     * @return bool
     */
    public function addColumnsToCType(array $fields, string $cType): ?bool
    {
        if (count($fields) < 1 || strlen('' . $cType) < 1) {
            return false;
        }
        $this->cType = $cType;
        foreach ($fields as $field) {
            if ($field['type'] == 'Collection') {
                if (!$this->addColumnsToCollectionTable($field)) {
                    return false;
                }
            }
            $this->tt_contentFields[] = $field;
        }
        return $this->updateDatabase();
    }

    /** protected function addColumnsToCollectionTable
     *
     * Collections columns need to be added recursivly. So we outsource that to a single method.
     *
     * @param array $collectionField
     * @return bool
    */
    protected function addColumnsToCollectionTable(array $collectionField): bool
    {
        if (!isset($collectionField['properties']['fields'])
                || !is_array($collectionField['properties']['fields'])
                || count($collectionField['properties']['fields']) < 1
        ) {
            return false;
        }
        foreach ($collectionField['properties']['fields'] as $field) {
            $field['collectionPath'] = '_' . $collectionField['identifier'];
            if ($field['type'] !== 'Collection') {
                if (!$this->addColumnsToCollectionTable($field)) {
                    return false;
                }
            }
            $this->collectionFields[] = $field;
        }
        return true;
    }

    /** protected function updateDatabase
     *
     * Looks if there are new columns to add and ads them.
     *
     * @return bool
    */
    protected function updateDatabase(): bool
    {
        if (count($this->tt_contentFields) < 1 && count($this->collectionFields) < 1) {
            return false;
        }

        $sqlTtContentStatementStart = 'CREATE TABLE tt_content (';
        $sqlCollectionStatementStart = 'CREATE TABLE ' . Constants::COLLECTION_FOREIGN_TABLE . ' (';
        $statementTtContent = $sqlTtContentStatementStart;
        $statementCollections = $sqlCollectionStatementStart;
        $dataService = GeneralUtility::makeInstance(DataService::class);

        if (count($this->tt_contentFields) > 0) {
            foreach ($this->tt_contentFields as $field) {
                $tempUniqueColumnName = $dataService->uniqueColumnName($this->cType, $field['identifier']);
                $fieldSql = $this->yamlTranslator->getSQL($tempUniqueColumnName, $field['type']);
                $statementTtContent .= (($statementTtContent !== $sqlTtContentStatementStart) ? ', ' : ' ') . $fieldSql;
            }
            $statementTtContent .= " );\n";
        }
        if (count($this->collectionFields) > 0) {
            foreach ($this->collectionFields as $field) {
                $tempUniqueColumnName = $field['collectionPath'] . '_' . $field['identifier'];
                $tempUniqueColumnName = $dataService->uniqueColumnName($this->cType, $tempUniqueColumnName);
                $fieldSql = $this->yamlTranslator->getSQL($tempUniqueColumnName, $field['type']);
                $statementCollections .=  (($statementCollections !== $sqlCollectionStatementStart) ? ', ' : ' ') . $fieldSql;
            }
            $statementCollections .= " );\n";
        }

        // add tt_content fields
        if ($statementTtContent !== $sqlTtContentStatementStart && count($this->tt_contentFields) > 0) {
            if (!$this->execDatabaseUpdateStatement($statementTtContent, 'tt_content')) {
                return false;
            }
        }
        // add collection fields
        if ($statementCollections !== $sqlCollectionStatementStart && count($this->collectionFields) > 0) {
            if (!$this->execDatabaseUpdateStatement($statementCollections, Constants::COLLECTION_FOREIGN_TABLE)) {
                return false;
            }
        }
        return true;
    }

    /** protected function execDatabaseUpdateStatement
     *
     * The database update statement must be like this:
     * CREATE TABLE tablename (column_name column_definition);
     *
     * Or multiple columns:
     * CREATE TABLE tablename (column_name1 column_definition1, column_name2 column_definition2);
     *
     * It is realy important that the statement is a CREATE TABLE statement.
     *
     * $table is the table name.
     *
     * @param string $statement
     * @param string $table
     * @return bool|array
     */
    protected function execDatabaseUpdateStatement(string $statement, string $table): ?bool
    {
        if (strlen('' . $table) < 1 || strlen('' . $statement) < 1) {
            return false;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $schemaMigrator = GeneralUtility::makeInstance(SchemaMigrator::class);
        $sqlUpdateSuggestion = $schemaMigrator->getUpdateSuggestions([0 => $statement]);

        $updateResult = true;

        foreach ($sqlUpdateSuggestion as $updateSuggestions) {
            unset($updateSuggestions['tables_count'], $updateSuggestions['change_currentValue']);
            $updateSuggestions = array_merge(...array_values($updateSuggestions));
            foreach ($updateSuggestions as $currentStatement) {
                if ($updateResult) {
                    try {
                        if (method_exists($connection, 'executeStatement')) {
                            $connection->executeStatement($currentStatement);
                        } else {
                            $connection->executeUpdate($currentStatement);
                        }
                        $updateResult = true;
                    } catch (Exception $exception) {
                        $updateResult = false;
                        return [
                            'error' => $exception->getPrevious()->getMessage()
                        ];
                    }
                }
            }
        }
        return $updateResult;
    }

    /** public function execDatabaseSqlStatement
     * Executes a sql $statement directly to the given $table.
     *
     * Returns true if successfull
     * Returns an array ['error' => 'error message'] if something went wrong.
     *
     * @param string $statement
     * @param string $table
     * @return bool|array
     */
    public function execDatabaseSqlStatement(string $statement, string $table): ?bool
    {
        if (strlen('' . $table) < 1 || strlen('' . $statement) < 1) {
            return false;
        }
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        try {
            if (method_exists($connection, 'executeStatement')) {
                $connection->executeStatement($statement);
            } else {
                $connection->executeUpdate($statement);
            }
            return true;
        } catch (Exception $exception) {
            return [
                'error' => $exception->getPrevious()->getMessage()
            ];
        }
        return true;
    }
}
