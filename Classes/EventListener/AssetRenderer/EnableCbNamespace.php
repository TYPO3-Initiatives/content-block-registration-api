<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\EventListener\AssetRenderer;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\Event\AbstractBeforeAssetRenderingEvent;
use TYPO3\CMS\Core\Page\Event\BeforeJavaScriptsRenderingEvent;
use TYPO3\CMS\Core\Page\Event\BeforeStylesheetsRenderingEvent;
use Typo3Contentblocks\ContentblocksRegApi\Constants;

/**
 * Rewrites CB:-paths in assets.
 * Works just like EXT:-paths are handled in @see GeneralUtility::getFileAbsFileName().
 */
class EnableCbNamespace
{
    /**
     * @var AssetCollector
     */
    protected $assetCollector;

    /**
     * @var AbstractBeforeAssetRenderingEvent
     */
    protected $event;

    /**
     * Rewrites paths or parts of paths in asset URIs.
     */
    public function __invoke(AbstractBeforeAssetRenderingEvent $event): void
    {
        if ($event->isInline()) {
            return;
        }

        $this->assetCollector = $event->getAssetCollector();
        $this->event = $event;

        if (is_a($event, BeforeStylesheetsRenderingEvent::class)) {
            $this->rewriteStylesheets();
        } elseif (is_a($event, BeforeJavaScriptsRenderingEvent::class)) {
            $this->rewriteJavaScripts();
        }
    }

    protected function rewriteJavaScripts(): void
    {
        $assets = $this->assetCollector->getJavaScripts($this->event->isPriority());
        foreach ($assets as $identifier => $asset) {
            $this->assetCollector->addJavaScript($identifier, $this->mapUri($asset['source']));
        }
    }

    protected function rewriteStylesheets(): void
    {
        $assets = $this->assetCollector->getStyleSheets($this->event->isPriority());
        foreach ($assets as $identifier => $asset) {
            // @extensionScannerIgnoreLine
            $this->assetCollector->addStyleSheet($identifier, $this->mapUri($asset['source']));
        }
    }

    protected function mapUri(string $uri): string
    {
        return preg_replace(
            '#^CB:#',
            preg_quote(Constants::BASEPATH, '#'),
            $uri
        );
    }
}
