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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Resource\FileCollector;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Replase the FelxformProcessor.
 * Moves the content to level 0, so integrators can use there variables diectly.
 *
 * TODO: decide, whether this class should be a separated class or if
 * this code should be migrated to general CbProcessor.
 */
class CbContentProcessor implements DataProcessorInterface
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
        // $cbConf = $this->configurationService->cbConfiguration($this->cType);
        // $processedData['cb'] = $cbConf;
        $this->cbConf = $processedData['cb'];

        $cbData = [];

        foreach ($this->cbConf['fields'] as $field) {
            if (count($field['_path']) == 1) {
                $cbData = $this->_processField($field, $this->record, $cbData);
            }
        }

        // TODO: Check what is about localization?
        // $maybeLocalizedUid = (int)(
        //     $this->record['_LOCALIZED_UID'] ?? $this->record['uid']
        // );

        $processedData = array_merge($processedData, $cbData);
        return $processedData;
    }

    /** process a field
     * @var array $fieldConf, configuration of the field
     * @var array $record, the data base record (row) with the values inside
     * @var array $cbData, the data stack where to add the data
     * @return array|string|int
    */
    protected function _processField(array $fieldConf, array $record, array $cbData)
    {
        $fieldColumnName = $this->dataService->uniqueColumnName($this->cbConf['key'], $fieldConf['_identifier']);
        // Get normal fields
        if ( !array_key_exists($fieldConf['_identifier'], $this->cbConf['collectionFields'])
                && !array_key_exists($fieldConf['_identifier'], $this->cbConf['fileFields'])
        ) {
            $cbData[$fieldConf['identifier']] = $record[$fieldColumnName];
        }
        // get file fields
        else if ( array_key_exists($fieldConf['_identifier'], $this->cbConf['fileFields']) ){
            $files = $this->_getFiles (
                $fieldConf['_identifier'],
                ((count($fieldConf['_path']) == 1) ? 'tt_content' : Constants::COLLECTION_FOREIGN_TABLE ),
                $record
            );

            if ($fieldConf['properties']['minItems'] == 1 && $fieldConf['properties']['maxItems'] == 1) {
                $files = $files[0];
            }
            $cbData[$fieldConf['identifier']] = $files;
        }
        // handle collections
        else if ($fieldConf['type'] == 'Collection'){
            $cbData[$fieldConf['identifier']] = $this->_processCollection(
                ((count($fieldConf['_path']) == 1) ? 'tt_content' : Constants::COLLECTION_FOREIGN_TABLE),
                $record['uid'],
                $fieldConf
            );
        }
        return $cbData;
    }

    /* find file for a field */
    protected function _getFiles($fieldName, $table, $record): array
    {
        // gather data
        /** @var FileCollector $fileCollector */
        $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
        $fileCollector->addFilesFromRelation($table, $fieldName, $record);
        return $fileCollector->getFiles();
    }

    /**
     * Manage collections and sub fields.
     * All collection data is stored in the table tx_contentblocks_reg_api_collection.
     */
    protected function _processCollection(string $parentTable, int $parentUid, array $parentFieldConf): array
    {
        // check if collection can be processed
        if ( !isset($parentFieldConf['properties']['fields']) || !is_array($parentFieldConf['properties']['fields'])){
            return [];
        }

        $q = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(Constants::COLLECTION_FOREIGN_TABLE)
            ->createQueryBuilder();
        $q->getRestrictions()->add(GeneralUtility::makeInstance(WorkspaceRestriction::class));
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
            )->orderBy('sorting')
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
            foreach ($parentFieldConf['properties']['fields'] as $fieldConf) {
                $collectionData = $this->_processField($fieldConf, $r, $collectionData);
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
