<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Backend\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use Typo3Contentblocks\ContentblocksRegApi\DataProcessing\CbProcessor;
use Typo3Contentblocks\ContentblocksRegApi\DataProcessing\FlexFormProcessor;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;

/**
 * Sets up Fluid and applies the same DataProcessor as the frontend to the data record.
 * Wraps the backend preview in class="cb-editor".
 */
class PreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * @var FlexFormProcessor
     */
    protected $flexFormProcessor;

    /**
     * @var CbProcessor
     */
    protected $cbProcessor;

    /**
     * @var ContentObjectRenderer
     */
    protected $cObj;

    public function __construct(
        ContentObjectRenderer $cObj,
        FlexFormProcessor $flexFormProcessor,
        CbProcessor $cbProcessor
    ) {
        $this->cObj = $cObj;
        $this->flexFormProcessor = $flexFormProcessor;
        $this->cbProcessor = $cbProcessor;
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        $cbConfiguration = ConfigurationService::cbConfiguration($record['CType']);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($cbConfiguration['EditorPreview.html']);

        // TODO use TypoScript configuration for paths
        $view->setPartialRootPaths([$cbConfiguration['srcPath']]);
        $view->setLayoutRootPaths([$cbConfiguration['srcPath']]);

        $view->assign('data', $record);

        $processedData = ['data' => $record];
        // TODO use TypoScript configuration for DataProcessors
        // CB configuration
        $processedData = $this->cbProcessor
            ->process(
                $this->cObj,
                [],
                [],
                $processedData
            );
        // Flexform
        $processedData = $this->flexFormProcessor
            ->process(
                $this->cObj,
                [],
                [],
                $processedData
            );

        $view->assignMultiple($processedData);

        // TODO the wrapping class should go to a proper Fluid layout
        return '<div class="cb-editor">' . $view->render() . '</div>';
    }

    public function wrapPageModulePreview(
        string $previewHeader,
        string $previewContent,
        GridColumnItem $item
    ): string {
        return $previewHeader . $previewContent;
    }
}
