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
use TYPO3\CMS\Core\SingletonInterface;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;
use Typo3Contentblocks\ContentblocksRegApi\Service\YamlToSqlTranslationService;

/**
 * Class SqlGenerator
 * This Class adds the contentblock configuration to the compare database statement in the InstallTool.
 */
class SqlGenerator implements SingletonInterface
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var YamlToSqlTranslationService
     */
    protected $yamlTranslator;

    /**
     * @var DataService
     */
    protected $dataService;

    public function __construct(ConfigurationService $configurationService, YamlToSqlTranslationService $yamlTranslator, DataService $dataService)
    {
        $this->configurationService = $configurationService;
        $this->yamlTranslator = $yamlTranslator;
        $this->dataService = $dataService;
    }

    /**
     * returns sql statements of all elements
     */
    protected function getSqlByConfiguration(): array
    {
        $sql = [];
        $sqlStatementReset = 'CREATE TABLE tt_content (';
        $sqlCollectionStatementReset = 'CREATE TABLE ' . Constants::COLLECTION_FOREIGN_TABLE . ' (';
        $collectionFields = [];

        $configuration = $this->configurationService->configuration();

        if (is_array($configuration)) {
            foreach ($configuration as $contentBlock) {
                if (is_array($contentBlock['fields'])
                    && count($contentBlock['fields']) > 0
                ) {
                    $fieldsList = $contentBlock['fields'];
                    $sqlStatement = $sqlStatementReset;
                    $sqlCollectionStatement = $sqlCollectionStatementReset;

                    foreach ($fieldsList as $field) {
                        // Feature: reuse of existing fields
                        if (
                            (
                                // check tt_content
                                isset($field['properties']['useExistingField'])
                                && $field['properties']['useExistingField'] === true
                                // check if there is a column configuration
                                && array_key_exists($field['identifier'], $GLOBALS['TCA']['tt_content']['columns'])
                            ) || (
                                // check collection
                                isset($field['identifier'])
                                && isset($field['type'])
                                && count($field['_path']) > 1
                                && isset($field['properties']['useExistingField'])
                                && $field['properties']['useExistingField'] === true
                                && array_key_exists($field['identifier'], $GLOBALS['TCA'][Constants::COLLECTION_FOREIGN_TABLE]['columns'])
                            )
                        ) {
                            continue;
                        }

                        $tempUniqueColumnName = $this->dataService->uniqueColumnName($contentBlock['key'], $field['_identifier']);

                        // Add fields to tt_content (first level)
                        if (isset($field['_identifier']) && isset($field['type']) && count($field['_path']) == 1) {
                            $fieldSql = $this->yamlTranslator
                                ->getSQL($tempUniqueColumnName, $field['type']);
                            // check if field is supported by the yamlTranslator
                            if (strlen('' . $fieldSql) > 3) {
                                $sqlStatement .= (($sqlStatement !== $sqlStatementReset) ? ',' : '') . ' ' . $fieldSql;
                            }
                        }

                        // Add collection fields
                        elseif (isset($field['_identifier']) && isset($field['type']) && count($field['_path']) > 1) {
                            $fieldSql = $this->yamlTranslator
                                ->getSQL($tempUniqueColumnName, $field['type']);
                            // check if field is supported by the yamlTranslator
                            if (strlen('' . $fieldSql) > 3) {
                                $sqlCollectionStatement .= (($sqlCollectionStatement !== $sqlCollectionStatementReset) ? ',' : '') . ' ' . $fieldSql;
                            }
                            // TODO: else throw usefull exeption if not supported
                        }
                    }
                    // avoid empty create statements
                    if ($sqlStatement !== $sqlStatementReset) {
                        $sql[] = $sqlStatement . ");\n";
                    }
                    if ($sqlCollectionStatement !== $sqlCollectionStatementReset) {
                        $collectionFields[] = $sqlCollectionStatement . ");\n";
                    }
                }
            }
        }

        $sql = array_merge($sql, $collectionFields);
        return $sql;
    }

    /**
     * Adds the SQL for all elements to the psr-14 AlterTableDefinitionStatementsEvent event.
     *
     * @param AlterTableDefinitionStatementsEvent $event
     */
    public function addDatabaseTablesDefinition(AlterTableDefinitionStatementsEvent $event): void
    {
        $event->setSqlData(array_merge($event->getSqlData(), $this->getSqlByConfiguration()));
    }
}
