<?php
defined('TYPO3_MODE') or die();

// Add some fields to FE Users table to show TCA fields definitions
// USAGE: TCA Reference > $GLOBALS['TCA'] array reference > ['columns'][fieldname]['config'] / TYPE: "select"
$temporaryColumns = array (
        'content_block' => array (
                'exclude' => 0,
                'label' => 'LLL:EXT:sci_api/Resources/Private/Language/locallang_db.xlf:tt_content.content_block',
                'config' => array (
                    'type' => 'input',
            )
        ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        $temporaryColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'content_block'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'appearanceLinks',
    'content_block',
    'after:linkToTop'
);