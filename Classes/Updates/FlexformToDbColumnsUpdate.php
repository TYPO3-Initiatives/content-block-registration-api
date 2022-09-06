<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Updates;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DatabaseService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;

/**
 * Class FlexformToDbColumnsUpdate
 *
 * After refactoring the storage method from flexform to database columns, we server a
 * tool to transform existing content blocks to the new data storage.
 */
class FlexformToDbColumnsUpdate implements UpgradeWizardInterface, RepeatableInterface, LoggerAwareInterface
{
    // Use LoggerAwareTrait in your class to automatically instantiate $this->logger.
    // https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Logging/Quickstart/Index.html
    use LoggerAwareTrait;

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'contentblocksRegApi_flexformToDbColumnsUpdate';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return '[Contentblocks Registration API] Move Flexform to database columns';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'After testing periot, we moved data storage from flexform technology to database tables. If you see this suggestion, you might have some old content block flexforms in your system.';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        // get all fields
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class)->configuration();
        $dataService = GeneralUtility::makeInstance(DataService::class);
        $ttContentColumns = [];
        $ttContentUpdateString = '';
        $collectionColumns = [];
        $collectionUpdateString = '';
        foreach ($configuration as $contentBlock) {
            if (is_array($contentBlock['fields'])
                && count($contentBlock['fields']) > 0
            ) {
                $fieldsList = $contentBlock['fields'];
                foreach ($fieldsList as $field) {
                    $tempUniqueColumnName = $dataService->uniqueColumnName($contentBlock['key'], $field['_identifier']);

                    // Add fields to tt_content (first level)
                    if (isset($field['_identifier']) && isset($field['type']) && count($field['_path']) == 1 && !isset($ttContentColumns[$tempUniqueColumnName])) {
                        $ttContentColumns[$tempUniqueColumnName] = $field['_identifier'];
                        $ttContentUpdateString .= ((strlen('' . $ttContentUpdateString) > 1) ? ' , ' : '') . $tempUniqueColumnName . ' = ' . $this->_getSqlFieldMatchingPart($field);
                    }

                    // Add collection fields
                    elseif (isset($field['_identifier']) && isset($field['type']) && count($field['_path']) > 1 && !isset($collectionColumns[$tempUniqueColumnName])) {
                        $collectionColumns[$tempUniqueColumnName] = $field['_identifier'];
                        $collectionUpdateString .= ((strlen('' . $collectionUpdateString) > 1) ? ', ' : '') . $tempUniqueColumnName . ' = ' . $this->_getSqlFieldMatchingPart($field);
                    }
                }
            }
        }

        // check if there is something to do (there should be something to do)
        if (
            (count($ttContentColumns) < 1 && count($collectionColumns) < 1)
            || (strlen('' . $collectionUpdateString) < 1 && strlen('' . $ttContentUpdateString) < 1)
        ) {
            return false;
        }

        $databaseService = GeneralUtility::makeInstance(DatabaseService::class);

        // update tt_content
        $sql = '';
        if (count($ttContentColumns) > 0) {
            $sql = 'UPDATE tt_content SET ' . $ttContentUpdateString . ' WHERE ' . Constants::FLEXFORM_FIELD . ' LIKE \'' . Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS . '\';';
            $result = $databaseService->execDatabaseSqlStatement($sql, 'tt_content');
            if ($result !== true) {
                $error = ((is_array($result) && isset($result['error'])) ? $result['error'] : '');
                // @extensionScannerIgnoreLine
                $this->logger->error('Could not update tt_content in class ' . get_class($this) . ':' . __LINE__ . '.', [
                    'sql' => $sql,
                    'errorMsg' => $error
                ]);
                return false;
            }
        }
        // update tx_contentblocks_reg_api_collection
        $sql = '';
        if (count($collectionColumns) > 0) {
            $sql = 'UPDATE ' . Constants::COLLECTION_FOREIGN_TABLE . ' SET ' . $collectionUpdateString . ' WHERE ' . Constants::FLEXFORM_FIELD . ' LIKE \'' . Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS . '\';';
            $result = $databaseService->execDatabaseSqlStatement($sql, Constants::COLLECTION_FOREIGN_TABLE);
            if ($result !== true) {
                $error = ((is_array($result) && isset($result['error'])) ? $result['error'] : '');
                // @extensionScannerIgnoreLine
                $this->logger->error('Could not update ' . Constants::COLLECTION_FOREIGN_TABLE . ' in class ' . get_class($this) . ':' . __LINE__ . '.', [
                    'sql' => $sql,
                    'errorMsg' => $error
                ]);
                return false;
            }
        }

        // move collections from Flexform to database column
        $sql = 'UPDATE ' . Constants::COLLECTION_FOREIGN_TABLE . '
            SET ' . Constants::COLLECTION_FOREIGN_MATCH_FIELD . ' =
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(' . Constants::COLLECTION_FOREIGN_MATCH_FIELD . ', \'typo3-contentblocks_\', \'cb_\'),
                        \'|\', \'_\'),
                    \'-\', \'_\'),
                \'.\', \'_\')
            WHERE ' . Constants::FLEXFORM_FIELD . ' LIKE \'' . Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS . '\';';
        $result = $databaseService->execDatabaseSqlStatement($sql, Constants::COLLECTION_FOREIGN_TABLE);
        if ($result !== true) {
            $error = ((is_array($result) && isset($result['error'])) ? $result['error'] : '');
            // @extensionScannerIgnoreLine
            $this->logger->error('Could not update ' . Constants::COLLECTION_FOREIGN_TABLE . ' in class ' . get_class($this) . ':' . __LINE__ . ' while process existing collections from flexform to database table columns.', [
                'sql' => $sql,
                'errorMsg' => $error
            ]);
            return false;
        }

        // update sys_file_reference: this seems to work out of the box.

        // set tt_content.content_block to '\<?xml version="1.0" encoding="utf-8" standalone="yes" ?\>'
        $sql = 'UPDATE tt_content SET ' . Constants::FLEXFORM_FIELD . ' = \'\<?xml version="1.0" encoding="utf-8" standalone="yes" ?\>\' WHERE ' . Constants::FLEXFORM_FIELD . ' LIKE \'' . Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS . '\';';
        $result = $databaseService->execDatabaseSqlStatement($sql, 'tt_content');
        if ($result !== true) {
            $error = ((is_array($result) && isset($result['error'])) ? $result['error'] : '');
            // @extensionScannerIgnoreLine
            $this->logger->error('Could not reset tt_content.' . Constants::FLEXFORM_FIELD . ' flexform in class ' . get_class($this) . ':' . __LINE__ . '.', [
                'sql' => $sql,
                'errorMsg' => $error
            ]);
            return false;
        }

        // set tx_contentblocks_reg_api_collection.content_block to NULL
        $sql = 'UPDATE ' . Constants::COLLECTION_FOREIGN_TABLE . ' SET ' . Constants::FLEXFORM_FIELD . ' = NULL WHERE ' . Constants::FLEXFORM_FIELD . ' LIKE \'' . Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS . '\';';
        $result = $databaseService->execDatabaseSqlStatement($sql, Constants::COLLECTION_FOREIGN_TABLE);
        if ($result !== true) {
            $error = ((is_array($result) && isset($result['error'])) ? $result['error'] : '');
            // @extensionScannerIgnoreLine
            $this->logger->error('Could not reset ' . Constants::COLLECTION_FOREIGN_TABLE . '.' . Constants::FLEXFORM_FIELD . ' flexform in class ' . get_class($this) . ':' . __LINE__ . ' while process existing collections from flexform to database table columns.', [
                'sql' => $sql,
                'errorMsg' => $error
            ]);
            return false;
        }

        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        // check if something todo in the tt_content table
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $elementCount = $queryBuilder->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like(
                    Constants::FLEXFORM_FIELD,
                    $queryBuilder->createNamedParameter(Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS)
                )
            )
            ->execute()->fetchColumn(0);
        if ((bool)$elementCount) {
            return (bool)$elementCount;
        }

        // check if something to do in tx_contentblocks_reg_api_collection
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Constants::COLLECTION_FOREIGN_TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $elementCount = $queryBuilder->count('uid')
            ->from(Constants::COLLECTION_FOREIGN_TABLE)
            ->where(
                $queryBuilder->expr()->like(
                    Constants::FLEXFORM_FIELD,
                    $queryBuilder->createNamedParameter(Constants::UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS)
                )
            )
            ->execute()->fetchColumn(0);
        if ((bool)$elementCount) {
            return (bool)$elementCount;
        }

        return false;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    /**
     * Returns the SQL statement part, which maps the flexform data to the db table column. Including mySQL casting.
     *
     * @param array $fieldConf
     * @return string
     */
    protected function _getSqlFieldMatchingPart(array $fieldConf): string
    {
        $castText = 'ExtractValue(content_block, \'//field[@index="' . $fieldConf['_identifier'] . '"]/value\')';
        $castInteger = 'CAST( CONCAT(\'0\',  ExtractValue(content_block, \'//field[@index="' . $fieldConf['_identifier'] . '"]/value\')) AS SIGNED)';
        $castDouble = 'CAST( CONCAT(\'0\',  ExtractValue(content_block, \'//field[@index="' . $fieldConf['_identifier'] . '"]/value\')) AS DECIMAL(30,2))';

        // special solution for HTML text
        $castHtmlText = 'REPLACE( REPLACE( REPLACE( REPLACE( REPLACE(
                REPLACE(' . $castText . ', \'&apos;\',\'"\')
            , \'&amp;\',\'&\')
            , \'&lt;\',\'<\')
            , \'&gt;\',\'>\')
            , \'&nbsp;\',\' \')
            , \'&quot;\',\'"\')';
        if (
            ($fieldConf['type'] == 'Textarea' || $fieldConf['type'] == 'TextMultiline')
            && $fieldConf['properties']['enableRichtext'] === true
        ) {
            return $castHtmlText;
        }

        switch ($fieldConf['type']) {
            case 'Checkbox':
                return $castText;
            case 'Collection':
                return $castInteger;
            case 'Color':
                return $castText;
            case 'Date':
            case 'DateTime':
                return $castInteger;
            case 'Email':
                return $castText;
            case 'Icon': // Icon was supported in v1, so we should migrate that to image
            case 'Image':
            case 'Integer':
                return $castInteger;
            case 'Money':
                return $castDouble;
            case 'MultiSelect':
                return $castText;
            case 'Number':
            case 'Percent':
                return $castInteger;
            case 'Radiobox':
            case 'Select':
            case 'Tel':
            case 'Text':
            case 'Textarea':
            case 'TextMultiline':
                return $castText;
            case 'Time':
                return $castInteger;
            case 'Toggle':
            case 'Url':
                return $castText;
            default:
                return $castText; // TODO: throw exception not supported field type (column type).
        }
    }
}
