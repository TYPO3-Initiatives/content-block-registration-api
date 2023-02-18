<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\DataProcessing;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Resource\FileCollector;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;

/**
 * Adds information about the current content block to variable "cb".
 */
class CbProcessor implements DataProcessorInterface
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var DataService
     */
    protected $dataService;

    /**
     * @var string
     */
    protected $cType;

    /**
     * @var array
     */
    protected $record;

    /**
     * @var array
     */
    protected $cbConf;

    public function __construct(
        ConfigurationService $configurationService,
        DataService $dataService
    ) {
        $this->configurationService = $configurationService;
        $this->dataService = $dataService;
    }

    /**
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $this->record = $processedData['data'];
        $this->cType = $this->record['CType'];
        $cbConf = $this->configurationService->cbConfiguration($processedData['data']['CType']);
        $this->cbConf = $cbConf;
        $processedData['cb'] = $cbConf;

        $cbData = [];

        foreach ($this->cbConf['fields'] as $field) {
            if (count($field['_path']) == 1) {
                $cbData = $this->_processField($field, $this->record, $cbData);
            }
        }

        $processedData = array_merge($processedData, $cbData);
        return $processedData;
    }

    /** process a field
     * @throws \Exception
     * @var array $fieldConf, configuration of the field
     * @var array $record, the data base record (row) with the values inside
     * @var array $cbData, the data stack where to add the data
     * @return array|string|int
    */
    protected function _processField(array $fieldConf, array $record, array $cbData)
    {
        $fieldColumnName = $this->dataService->uniqueColumnName($this->cbConf['key'], $fieldConf['_identifier']);
        // Get normal fields
        if (!array_key_exists($fieldConf['_identifier'], $this->cbConf['collectionFields'])
                && !array_key_exists($fieldConf['_identifier'], $this->cbConf['fileFields'])
        ) {
            // Feature: reuse of existing fields
            if (
                isset($fieldConf['properties']['useExistingField'])
                && $fieldConf['properties']['useExistingField'] === true
                // check if there is a column configuration, otherwice there is a content block field
                && (
                    array_key_exists($fieldConf['identifier'], $GLOBALS['TCA']['tt_content']['columns'])
                    || array_key_exists($fieldConf['identifier'], $GLOBALS['TCA'][Constants::COLLECTION_FOREIGN_TABLE]['columns'])
                )
            ) {
                if (!array_key_exists($fieldConf['identifier'], $record)) {
                    throw new \Exception(sprintf('It seems your field %s is missing in the database. Maybe a database compare could help you out.', $fieldConf['identifier']));
                }
                $cbData[$fieldConf['identifier']] = $record[$fieldConf['identifier']];
            } else {
                if (!array_key_exists($fieldColumnName, $record)) {
                    throw new \Exception(sprintf('It seems your field %s is missing in the database. Maybe a database compare could help you out.', $fieldColumnName));
                }
                // The "normal" way
                $cbData[$fieldConf['identifier']] = $record[$fieldColumnName];
            }
        }
        // get file fields
        elseif (array_key_exists($fieldConf['_identifier'], $this->cbConf['fileFields'])) {
            $isUseExistingField = isset($fieldConf['properties']['useExistingField']) && $fieldConf['properties']['useExistingField'] === true;
            $files = $this->_getFiles(
                (($this->_isFrontend() || $isUseExistingField) ? $fieldConf['_identifier'] : $fieldColumnName),
                ((count($fieldConf['_path']) == 1) ? 'tt_content' : Constants::COLLECTION_FOREIGN_TABLE),
                $record
            );

            if (
                (isset($fieldConf['properties']['minItems']) && $fieldConf['properties']['minItems'] == 1) &&
                (isset($fieldConf['properties']['maxItems']) && $fieldConf['properties']['maxItems'] == 1)
            ) {
                $files = array_pop(array_reverse($files));
            }
            $cbData[$fieldConf['identifier']] = $files;
        }
        // handle collections
        elseif ($fieldConf['type'] == 'Collection') {
            $cbData[$fieldConf['identifier']] = $this->_processCollection(
                ((count($fieldConf['_path']) == 1) ? 'tt_content' : Constants::COLLECTION_FOREIGN_TABLE),
                $record['_LOCALIZED_UID'] ?? $record['uid'],
                $fieldConf
            );
        }
        return $cbData;
    }

    /**
     * find file for a field
     */
    protected function _getFiles($fieldName, $table, $record): array
    {
        // gather data
        if ($this->_isFrontend()) {
            /** @var FileCollector $fileCollector */
            $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
            $fileCollector->addFilesFromRelation($table, $fieldName, $record);
            return $fileCollector->getFiles();
        }
        // Since bug in FileCollector, we need to handle files the other way in backend to support workspaces.
        // https://review.typo3.org/c/Packages/TYPO3.CMS/+/74185
        // This should be removed after dropping support for v10.4
        $workspaceId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id', 0);
        $files = BackendUtility::resolveFileReferences(
            $table,
            $fieldName,
            $record,
            (($workspaceId !== 0) ? $workspaceId : null)
        );
        if ($files instanceof FileReference) {
            return [$files];
        }
        $files = array_reverse($files);
        return $files;
    }

    /**
     * Manage collections and sub fields.
     * All collection data is stored in the table tx_contentblocks_reg_api_collection.
     */
    protected function _processCollection(string $parentTable, int $parentUid, array $parentFieldConf): array
    {
        // check if collection can be processed
        if (!isset($parentFieldConf['properties']['fields']) || !is_array($parentFieldConf['properties']['fields'])) {
            return [];
        }

        // Managing Workspace overlays
        $workspaceId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id', 0);

        $q = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(Constants::COLLECTION_FOREIGN_TABLE)
            ->createQueryBuilder();

        $q->getRestrictions()->add(
            GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspaceId)
        );

        $stmt = $q->select('*')
            ->from(Constants::COLLECTION_FOREIGN_TABLE)
            ->where(
                $q->expr()->eq(
                    Constants::COLLECTION_FOREIGN_FIELD,
                    $q->createNamedParameter($parentUid, Connection::PARAM_INT)
                )
            )->andWhere(
                $q->expr()->eq(
                    Constants::COLLECTION_FOREIGN_TABLE_FIELD,
                    $q->createNamedParameter($parentTable)
                )
            )
            ->orderBy('sorting')
            ->execute();

        $fieldData = [];

        while ($r = $stmt->fetch()) {
            // overlay workspaces
            if ($this->_isFrontend()) {
                GeneralUtility::makeInstance(PageRepository::class)
                    ->versionOL(Constants::COLLECTION_FOREIGN_TABLE, $r);
                if (false === $r) {
                    continue;
                }
            } else {
                BackendUtility::workspaceOL(Constants::COLLECTION_FOREIGN_TABLE, $r);
                if (false === $r) {
                    continue;
                }
            }

            // dangling relation: validate relation
            if ($r[Constants::COLLECTION_FOREIGN_MATCH_FIELD] !== $this->dataService->uniqueColumnName($this->cbConf['key'], $parentFieldConf['_identifier'])) {
                continue;
            }

            $collectionData = [];
            // add the field infos
            foreach ($parentFieldConf['properties']['fields'] as $fieldConf) {
                $collectionData = $this->_processField($fieldConf, $r, $collectionData);
            }
            // add uid to collection items
            if (!array_key_exists('uid', $collectionData)) {
                $collectionData['uid'] = $r['uid'];
            }
            $fieldData[] = $collectionData;
        }

        return $fieldData;
    }

    protected function _isFrontend()
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController;
    }
}
