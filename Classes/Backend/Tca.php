<?php declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Backend;

use Sci\SciApi\Service\ConfigurationService;

class Tca
{
    /**
     * Create the TCA config for all Content Blocks
    **/
    public static function getTca()
    {
        $configuration = ConfigurationService::configuration();

        foreach ($configuration as $contentBlock) {

            /***************
             * Add Content Element
             */
            if (!is_array($GLOBALS['TCA']['tt_content']['types'][ $contentBlock['CType'] ])) {
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = [];
            }

            /***************
            * Assign Icon
            */
            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentBlock['CType']] = $contentBlock['CType'];

            /***************
             * Add content element to selector list
             */
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    'Content Element Text',
                    $contentBlock['CType'],
                    $contentBlock['CType']
                ],
                'header',
                'after'
            );

            /***************
             * Configure element type
             */
            $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = array_replace_recursive(
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']],
                [
                    'showitem' => '
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                        content_block,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                        --palette--;;language,
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                        --palette--;;hidden,
                        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                        categories,
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                        rowDescription,
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
                    '
                ]
            );

            /***************
             * Add flexForms for content element configuration
             */
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                'FILE:EXT:sci_api/Configuration/FlexForms/Text.xml',
                'content_block_text'
            );

            // foreach ($contentBlock['yaml']['fields'] as $field ) {
            //     createField($field, $contentBlock['CType']);
            // }
        }
    }

    /** parse single field */
    private function createField($field, $cType)
    {
        if (!is_array($field)) {
            return;
        } // if no array given, return
        // else if ( is_array($field['fields']) ) createField($field['fields']); // if is an array of fields
        else {
            // [ $field['identifier']
            if ($field['type'] === 'Text') {
                createInputField($field, $cType);
            }
        }
    }

    /** create textfield */
    private function createInputField($field, $cType)
    {
    }
}
