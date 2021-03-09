<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService;

class TcaGenerator
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * Get default flexform configuration of the tt_content.content_block field
     *
    */
    public function contentBlockFlexformColumnTca() :array
    {
        return [
            'exclude' => 0,
            'label' => 'LLL:EXT:contentblocks_reg_api/Resources/Private/Language/locallang_db.xlf:tt_content.content_block',
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
        ];
    }

    /**
     * Create the TCA config for all Content Blocks
     **/
    public function setTca() :void
    {
        $configuration = $this->configurationService->configuration();

        foreach ($configuration as $contentBlock) {
            /***************
             * Add Content Element
             */
            if (!is_array($GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']])) {
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = [];
            }

            // PreviewRenderer
            $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']]['previewRenderer'] =
                \Typo3Contentblocks\ContentblocksRegApi\Backend\Preview\PreviewRenderer::class;

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
                    'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                    . '.' . $contentBlock['package'] . '.title',
                    $contentBlock['CType'],
                    $contentBlock['CType'],
                ],
                'header',
                'after'
            );

            /***************
             * Configure element type
             */
            // Feature: enable pallette frame via extConf
            $enableLayoutOptions = (bool)\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
                            ->get('contentblocks_reg_api', 'enableLayoutOptions');
            $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = array_replace_recursive(
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']],
                [
                    'showitem' => '
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                            --palette--;;general,
                            header,
                            content_block,
                        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,' . (($enableLayoutOptions) ? '
                            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,' : '') . '
                            --palette--;;appearanceLinks,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                            --palette--;;language,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                            --palette--;;hidden,
                            --palette--;;access,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                            categories,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                            rowDescription,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
                    ',
                ]
            );

            /***************
             * Add flexForms for content element configuration
             */
            GeneralUtility::makeInstance(FlexFormGenerator::class)
                ->createFlexform($contentBlock);
        }
    }
}
