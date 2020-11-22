<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\DataProcessing;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
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
     * @var FlexFormService
     */
    protected $flexFormService;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

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


    public function __construct(
        FlexFormService $flexFormService,
        FileRepository $fileRepository,
        DataService $dataService
    ) {
        $this->flexFormService = $flexFormService;
        $this->fileRepository = $fileRepository;
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
    ): array {
        $this->record = $processedData['data'];
        $cbDataFlexForm = $this->record[Constants::FLEXFORM_FIELD];
        if (!is_string($cbDataFlexForm)) {
            return $processedData;
        }
        $this->cType = $this->record['CType'];

        $cbData = $this->flexFormService->convertFlexFormContentToArray($cbDataFlexForm);

        $this->_processCollections($cbData);
        $this->_processFiles($cbData);

        $processedData = array_merge($processedData, $cbData);
        return $processedData;
    }

    protected function _processCollections(array &$cbData): void
    {
        $collectionFields = ConfigurationService::cbCollectionFields($this->cType);

        foreach ($collectionFields as $combinedFieldIdentifier => $fieldConf) {
            $maybeLocalizedUid = $this->record['_LOCALIZED_UID']
                ?? $this->record['uid'];

            $parentUid = $maybeLocalizedUid;
            $parentTable = count($fieldConf['_path']) > 2
                ? Constants::COLLECTION_FOREIGN_TABLE
                : 'tt_content';

            $q = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable(Constants::COLLECTION_FOREIGN_TABLE)
                ->createQueryBuilder();
            $stmt = $q->select(
                'uid',
                Constants::COLLECTION_FOREIGN_FIELD,
                Constants::COLLECTION_FOREIGN_TABLE_FIELD,
                Constants::FLEXFORM_FIELD
            )
                ->from(Constants::COLLECTION_FOREIGN_TABLE)
                ->where(
                    $q->expr()->eq(
                        Constants::COLLECTION_FOREIGN_FIELD,
                        $q->createNamedParameter($parentUid, Connection::PARAM_INT)
                    )
                )->andWhere(
                    $q->expr()->eq(
                        Constants::COLLECTION_FOREIGN_MATCH_FIELD,
                        $q->createNamedParameter($this->cType . '_' . $combinedFieldIdentifier)
                    )
                )->andWhere(
                    $q->expr()->eq(
                        Constants::COLLECTION_FOREIGN_TABLE_FIELD,
                        $q->createNamedParameter($parentTable)
                    )
                )->orderBy('sorting')
                ->execute();

            $fieldData = [];
            while ($r = $stmt->fetchAssociative()) {
                $_flexformData = $this->flexFormService->convertFlexFormContentToArray(
                        $r[Constants::FLEXFORM_FIELD]
                    )[$fieldConf['identifier']] ?? [];
                $fieldData[] = $_flexformData;
            }

            // Deliver a single Collection if the field is configured as maxItems=1
            $maxItems = (int)($fieldConf['properties']['maxItems'] ?? null);
            if ($maxItems === 1) {
                $fieldData = $fieldData[0] ?? null;
            }

            $this->dataService->setData($cbData, $fieldConf['_path'], $fieldData);
        }
    }

    protected function _processFiles(array &$cbData): void
    {
        $fileFields = ConfigurationService::cbFileFields($this->cType);
        foreach ($cbData as $fieldIdentifier => $val) {
            if (in_array($fieldIdentifier, $fileFields)) {
                $maybeLocalizedUid = $processedData['data']['_LOCALIZED_UID']
                    ?? $this->record['uid'];

                // look away now

                // Why are you still looking?!
                if (!($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
                    /**
                     * @see \TYPO3\CMS\Core\Resource\FileRepository::findByRelation() requires
                     * a configured TCA column in backend context.
                     * That's impossible for a field inside a FlexForm.
                     * @see \TYPO3\CMS\Core\Resource\AbstractRepository::getEnvironmentMode()
                     */
                    $_tsfe = $GLOBALS['TSFE'] ?? null;
                    $tsfe = unserialize(
                        'O:58:"TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController":0:{}'
                    );
                    $GLOBALS['TSFE'] = $tsfe;
                }

                // welcome back
                $processedData[$fieldIdentifier] = $this->fileRepository->findByRelation(
                    'tt_content',
                    $fieldIdentifier,
                    $maybeLocalizedUid
                );

                // Deliver a single file if the field is configured as maxItems=1
                $fieldConf = ConfigurationService::cbField($this->cType, $fieldIdentifier);
                $maxItems = (int)($fieldConf['properties']['maxItems'] ?? 1);
                if ($maxItems === 1) {
                    $processedData[$fieldIdentifier] = $processedData[$fieldIdentifier][0] ?? null;
                }

                // look away again
                if (isset($_tsfe)) {
                    $GLOBALS['TSFE'] = $_tsfe;
                }
            }
        }
    }
}
