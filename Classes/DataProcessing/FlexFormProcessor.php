<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\DataProcessing;

use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;

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

    public function __construct()
    {
        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $this->fileRepository = GeneralUtility::makeInstance(FileRepository::class);
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
        $originalValue = $processedData['data']['content_block'];
        if (!is_string($originalValue)) {
            return $processedData;
        }

        $flexformData = $this->flexFormService->convertFlexFormContentToArray($originalValue);
        $processedData = array_merge($processedData, $flexformData);

        $relationFields = ConfigurationService::contentBlockConfiguration(
                $processedData['data']['CType']
            )['relationFields'] ?? [];

        foreach ($flexformData as $fieldKey => $val) {
            if (in_array($fieldKey, $relationFields)) {
                $maybeLocalizedUid = $processedData['data']['_LOCALIZED_UID']
                    ?? $processedData['data']['uid'];

                // look away now

                // Why are you still looking?!
                if (!($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
                    /**
                     * @see \TYPO3\CMS\Core\Resource\FileRepository::findByRelation() requires a configured TCA column
                     * in backend context. That's impossible for a field inside a FlexForm.
                     *
                     * @see \TYPO3\CMS\Core\Resource\AbstractRepository::getEnvironmentMode()
                     */
                    $_tsfe = $GLOBALS['TSFE'] ?? null;
                    $tsfe = unserialize(
                        'O:58:"TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController":0:{}'
                    );
                    $GLOBALS['TSFE'] = $tsfe;
                }

                // welcome back
                $processedData[$fieldKey] = $this->fileRepository->findByRelation(
                    'tt_content',
                    $fieldKey,
                    $maybeLocalizedUid
                );

                // look away again
                if (isset($_tsfe)) {
                    $GLOBALS['TSFE'] = $_tsfe;
                }
            }
        }

        return $processedData;
    }
}
