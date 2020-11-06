<?php

defined('TYPO3_MODE') || die('Access denied.');

(static function ($_EXTKEY = 'sci_api') {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE] = [];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE]['frontend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE]['frontend'] =
            \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    }

    $cbFinder = new \Symfony\Component\Finder\Finder();
    $cbFinder->directories()->in(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . \Sci\SciApi\Constants::BASEPATH);

    $contentBlockConfiguration = [];
    foreach ($cbFinder as $cbDir) {
        $cbIdentifier = $cbDir->getBasename();
        $contentBlockConfiguration [$cbIdentifier] = $cbDir->getRealPath();
    }

    $cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)
        ->getCache(\Sci\SciApi\Constants::CACHE);

    // unlimited lifetime
    $cache->set('contentBlockConfiguration', $contentBlockConfiguration, [], 0);
})();
