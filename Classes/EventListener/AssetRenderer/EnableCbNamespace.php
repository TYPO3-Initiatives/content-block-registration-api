<?php

declare(strict_types=1);

namespace Sci\SciApi\EventListener\AssetRenderer;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\Event\AbstractBeforeAssetRenderingEvent;
use TYPO3\CMS\Core\Page\Event\BeforeJavaScriptsRenderingEvent;
use TYPO3\CMS\Core\Page\Event\BeforeStylesheetsRenderingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
            $this->assetCollector->addStyleSheet($identifier, $this->mapUri($asset['source']));
        }
    }

    protected function mapUri(string $uri): string
    {
        // TODO
        $cbPublicPath = 'typo3conf'
            . DIRECTORY_SEPARATOR . 'contentBlocks' . DIRECTORY_SEPARATOR;
        $a = preg_replace('#^CB:#', preg_quote($cbPublicPath, '#'), $uri);
        return preg_replace('#^CB:#', preg_quote($cbPublicPath, '#'), $uri);
    }
}
