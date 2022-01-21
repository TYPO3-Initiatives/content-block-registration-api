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
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;

/**
 * Sets up Fluid and applies the same DataProcessor as the frontend to the data record.
 * Wraps the backend preview in class="cb-editor".
 */
class PreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * @var CbProcessor
     */
    protected $cbProcessor;

    /**
     * @var ContentObjectRenderer
     */
    protected $cObj;

    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var CbContentProcessor
    */
    protected $contentProcessor;

    public function __construct(
        ContentObjectRenderer $cObj,
        CbProcessor $cbProcessor,
        ConfigurationService $configurationService
    ) {
        $this->cObj = $cObj;
        $this->cbProcessor = $cbProcessor;
        $this->configurationService = $configurationService;
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        $cbConfiguration = $this->configurationService->cbConfiguration($record['CType']);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($cbConfiguration['EditorPreview.html']);

        // TODO: use TypoScript configuration for paths
        // TODO: add partialRootPath to cbConf
        $view->setPartialRootPaths(
            [
                'EXT:contentblocks_reg_api/Resources/Private/Partials/',
                $cbConfiguration['srcPath'],
            ]
        );
        $view->setLayoutRootPaths(
            [
                'EXT:contentblocks_reg_api/Resources/Private/Layouts/',
                $cbConfiguration['srcPath'],
            ]
        );

        $view->assign('data', $record);

        $processedData = ['data' => $record];
        // TODO use TypoScript configuration for DataProcessors
        // CB configuration & Database fields
        $processedData = $this->cbProcessor
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
