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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;

/**
 * Processes the FlexForm field and puts all entries as variables to the top level.
 */
class FlexFormProcessor implements DataProcessorInterface
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
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var FlexFormService
     */
    protected $flexFormService;

    /**
     * @var string
     */
    protected $cType;

    /**
     * @var array
     */
    protected $record;

    public function __construct(
        FlexFormService $flexFormService,
        FileRepository $fileRepository,
        DataService $dataService,
        ConfigurationService $configurationService
    ) {
        $this->flexFormService = $flexFormService;
        $this->fileRepository = $fileRepository;
        $this->dataService = $dataService;
        $this->configurationService = $configurationService;
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
    ): array {
        $this->record = $processedData['data'];
        $cbDataFlexForm = $this->record[Constants::FLEXFORM_FIELD];
        if (!is_string($cbDataFlexForm)) {
            return $processedData;
        }
        $this->cType = $this->record['CType'];

        $cbData = $this->flexFormService->convertFlexFormContentToArray($cbDataFlexForm);

        $maybeLocalizedUid = (int)(
            $this->record['_LOCALIZED_UID'] ?? $this->record['uid']
        );

        // process Images on highest level (tt_content)
        foreach ($this->configurationService->cbFileFieldsAtPath($this->cType, []) as $fieldConf) {
            $files = $this->_files('tt_content', $maybeLocalizedUid, $fieldConf['_identifier']);
            $cbData[end($fieldConf['_path'])] = $files;
        }

        $this->_processCollections($cbData, $maybeLocalizedUid);

        $processedData = array_merge($processedData, $cbData);
        return $processedData;
    }

    protected function _processCollections(array &$cbData, int $maybeLocalizedUid): void
    {
        $collections = $this->_collections('tt_content', $maybeLocalizedUid);

        ArrayUtility::mergeRecursiveWithOverrule($cbData, $collections);
    }

    protected function _collections(
        string $parentTable,
        int $parentUid,
        array $parentPath = []
    ): array {
        $collectionFields = $this->configurationService->cbCollectionFields($this->cType);
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

        // initialize collection fields
        $collections = [];
        foreach ($this->configurationService->cbCollectionFieldsAtPath($this->cType, $parentPath) as $fieldConf) {
            $collections[end($fieldConf['_path'])] = [];
        }

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
            [$cType, $combinedIdentifier] = $this->dataService->splitUniqueCombinedIdentifier(
                $r[Constants::COLLECTION_FOREIGN_MATCH_FIELD]
            );

            // dangling relation
            if ($cType !== $this->cType) {
                continue;
            }

            // dangling data structure
            if (!in_array($combinedIdentifier, array_keys($collectionFields))) {
                continue;
            }

            $path = $this->dataService->combinedIdentifierToArray($combinedIdentifier);
            $identifier = end($path);

            $fieldData = $this->flexFormService->convertFlexFormContentToArray(
                $r[Constants::FLEXFORM_FIELD]
            );
            $fieldData = $this->dataService->extractData($fieldData, $path) ?? [];

            // process Images on this level
            foreach ($this->configurationService->cbFileFieldsAtPath($this->cType, $path) as $fieldConf) {
                $files = $this->_files(
                    Constants::COLLECTION_FOREIGN_TABLE,
                    $r['uid'],
                    $fieldConf['_identifier']
                );
                $fieldData[end($fieldConf['_path'])] = $files;
            }

            $subCollections = $this->_collections(
                Constants::COLLECTION_FOREIGN_TABLE,
                $r['uid'],
                $path
            );

            ArrayUtility::mergeRecursiveWithOverrule($fieldData, $subCollections);
            $collections[$identifier][] = $fieldData;
        }

        return $collections;
    }

    /**
     * @param string $parentTable
     * @param int $parentUid
     * @param string $combinedIdentifier
     * @return array<FileReference>|FileReference|null
     */
    protected function _files(string $parentTable, int $parentUid, string $combinedIdentifier)
    {
        // look away now

        // Why are you still looking?!
        if (!$this->_isFrontend()) {
            /**
             * @see \TYPO3\CMS\Core\Resource\FileRepository::findByRelation() requires
             * a configured TCA column in backend context.
             * That's impossible for a field inside a FlexForm.
             * Yet because it is so incredibly convenient, we want to use it anyways...
             * @see \TYPO3\CMS\Core\Resource\AbstractRepository::getEnvironmentMode()
             */
            $_tsfe = $GLOBALS['TSFE'] ?? null;
            $tsfe = unserialize(
                'O:58:"TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController":0:{}'
            );
            $GLOBALS['TSFE'] = $tsfe;
        }

        // welcome back. Isn't that pretty?
        $files = $this->fileRepository->findByRelation(
            $parentTable,
            $combinedIdentifier,
            $parentUid
        );

        // Deliver a single file if the field is configured as maxItems=1
        $fieldConf = $this->configurationService->cbField($this->cType, $combinedIdentifier);
        $maxItems = (int)($fieldConf['properties']['maxItems'] ?? 1);
        if ($maxItems === 1) {
            $files = $files[0] ?? null;
        }

        // look away again
        if (isset($_tsfe)) {
            $GLOBALS['TSFE'] = $_tsfe;
        }

        return $files;
    }

    protected function _isFrontend()
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController;
    }
}
