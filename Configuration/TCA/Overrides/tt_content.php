<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

(static function ($_EXTKEY = 'contentblocks_reg_api') {
    $tcaGenerator = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        Typo3Contentblocks\ContentblocksRegApi\Generator\TcaGenerator::class
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'content_block' => $tcaGenerator->contentBlockFlexformColumnTca(),
        ]
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'content_block'
    );

    $tcaGenerator->setTca();
})();
