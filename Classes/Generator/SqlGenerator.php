<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * Class SqlGenerator
 * This Class adds the contentblock configuration to the compare database statement in the InstallTool.
 *
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Validator\ContentBlockValidator;
use Typo3Contentblocks\ContentblocksRegApi\Service\YamlToSqlTranslationService;

class SqlGenerator implements SingletonInterface
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var ContentBlockValidator
     */
    protected $contentBlockValidator;

    /**
     * @var YamlToSqlTranslationService
     */
    protected $yamlTranslator;

    public function __construct()
    {
        $this->contentBlockValidator = GeneralUtility::makeInstance(ContentBlockValidator::class);
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class, $this->contentBlockValidator);
        $this->yamlTranslator = GeneralUtility::makeInstance(YamlToSqlTranslationService::class);
    }

    /**
     * returns sql statements of all elements
     */
    protected function getSqlByConfiguration(): array
    {
        $sql = [];
        $sqlStatementReset = "CREATE TABLE tt_content (";
        $sqlCollectionStatementReset = "CREATE TABLE tx_contentblocks_reg_api_collection (";
        $collectionFields = [];

        $configuration = $this->configurationService->configuration();

        if ( is_array($configuration) ) {
            foreach ($configuration as $contentBlock) {
                if ( is_array($contentBlock['fields'])
                    && count($contentBlock['fields']) > 0
                ) {
                    $fieldsList = $contentBlock['fields'];
                    $sqlStatement = $sqlStatementReset;
                    $sqlCollectionStatement = $sqlCollectionStatementReset;

                    foreach ($fieldsList as $field) {

                        // Add fields to tt_content (first level)
                        if ( isset($field['_identifier']) && isset($field['type']) && count($field['_path']) == 1 ) {
                            $fieldSql = $this->yamlTranslator
                                ->getSQL($field['_identifier'], $contentBlock['key'], $field['type']);
                            // check if field is supported by the yamlTranslator
                            if ( strlen('' . $fieldSql) > 3 ) {
                                $sqlStatement .= (($sqlStatement !== $sqlStatementReset ) ? ',' : ''  ) . ' ' . $fieldSql;
                            }
                            // TODO: else throw usefull exeption if not supported
                        }

                        // Add collection fields
                        else if ( isset($field['_identifier']) && isset($field['type']) && count($field['_path']) > 1 ) {
                            $fieldSql = $this->yamlTranslator
                                ->getSQL( str_replace('.', '_', $field['_identifier'])  , $contentBlock['key'], $field['type']);
                            // check if field is supported by the yamlTranslator
                            if ( strlen('' . $fieldSql) > 3 ) {
                                $sqlCollectionStatement .= (($sqlCollectionStatement !== $sqlCollectionStatementReset ) ? ',' : ''  ) . ' ' . $fieldSql;
                            }
                            // TODO: else throw usefull exeption if not supported
                        }
                    }
                    // avoid empty create statements
                    if ($sqlStatement !== $sqlStatementReset) {
                        $sql[] = $sqlStatement . ");\n";
                    }
                    if ( $sqlCollectionStatement !== $sqlCollectionStatementReset ) {
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
