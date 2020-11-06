<?php declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') or die();

// Add some fields to FE Users table to show TCA fields definitions
// USAGE: TCA Reference > $GLOBALS['TCA'] array reference > ['columns'][fieldname]['config'] / TYPE: "select"
$temporaryColumns = [
        'content_block' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:sci_api/Resources/Private/Language/locallang_db.xlf:tt_content.content_block',
                'config' => [
                    'type' => 'flex',
                    'ds_pointerField' => 'CType',
                    'ds' => [
                        'default' => '
                            <T3DataStructure>
                              <ROOT>
                                <type>array</type>
                                <el>
                                    <!-- Repeat an element like "xmlTitle" beneath for as many elements you like. Remember to name them uniquely  -->
                                  <xmlTitle>
                                    <TCEforms>
                                        <label>The Title:</label>
                                        <config>
                                            <type>input</type>
                                            <size>48</size>
                                        </config>
                                    </TCEforms>
                                  </xmlTitle>
                                </el>
                              </ROOT>
                            </T3DataStructure>
                        '
                    ]
                ]
        ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    $temporaryColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'content_block'
);

Sci\SciApi\Backend\Tca::getTca();
