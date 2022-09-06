<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

use TYPO3\CMS\Core\Resource\File;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;

class FlexFormGenerator
{
    /**
     * @var DataService
     */
    protected $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * Create a flexform typolink
    */
    protected function createTypoLink(array $field, array $contentBlock): string
    {
        $blindLinkOption = 'page,url,mail,spec,file,folder,telephone';
        if (is_array($field['properties']['linkTypes'])) {
            foreach ($field['properties']['linkTypes'] as $allowedField) {
                $blindLinkOption = str_replace(str_replace('external', 'url', $allowedField), '', $blindLinkOption);
            }
        } else {
            $blindLinkOption = '';
        }

        $blindLinkFields = 'target,title,class,params';
        if (is_array($field['properties']['fieldTypes'])) {
            foreach ($field['properties']['fieldTypes'] as $allowedField) {
                $blindLinkFields = str_replace($allowedField, '', $blindLinkFields);
            }
        } else {
            $blindLinkFields = '';
        }

        return '
        <' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description</description>
                <config>
                    <type>input</type>
                    <renderType>inputLink</renderType>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '30') . '</size>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                    <fieldControl>
                        <linkPopup>
                            <options>
                                <blindLinkOptions>' . $blindLinkOption . '</blindLinkOptions>
                                <blindLinkFields>' . $blindLinkFields . '</blindLinkFields>
                            </options>
                        </linkPopup>
                    </fieldControl>
                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>
        ';
    }

    /**
     * Create a input field with several renderings: Mail, Integer,  Monay, Number, Range, Telephone, Date, Time, DateTime
    */
    protected function createInputField(array $field, array $contentBlock): string
    {
        $items = '';

        $evalFields = ($field['properties']['required'] === true ? 'required' : '') . ($field['properties']['trim'] === true && $field['properties']['required'] === true ? ',  ' : '') . ($field['properties']['trim'] === true ? 'trim ' : '');
        if ($field['type'] === 'Email') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'email';
        }
        if ($field['type'] === 'Integer') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'int';
        }
        if ($field['type'] === 'Money') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'double2';
        }
        if ($field['type'] === 'Number') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'num';
        }
        if ($field['type'] === 'Password') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'password';
        }
        if ($field['type'] === 'Range') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'trim,int';
        }
        if ($field['type'] === 'Tel') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'alphanum';
        }
        if ($field['type'] === 'Date') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'date';
        }
        if ($field['type'] === 'DateTime') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'datetime';
        }
        if ($field['type'] === 'Time') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'time';
        }

        $additionlConfig = '';
        if (is_array($field['properties']['range'])) {
            $additionlConfig .= '
            <range>
                <lower>' . (float)(
                (
                    $field['properties']['range']['lower'] !== ''
                        ? $field['properties']['range']['lower']
                        : '0'
                )
            ) . '</lower>
                <upper>' . (float)(
                (
                    $field['properties']['range']['upper'] !== ''
                        ? $field['properties']['range']['upper']
                        : '100'
                )
            ) . '</upper>
            </range>';
        }
        if ($field['type'] === 'Percent') {
            $additionlConfig .= $additionlConfig . '
            <slider>
                <step>' . ($field['properties']['slider']['step'] !== '' ? $field['properties']['slider']['step'] : '1') . '</step>
                <width>' . ($field['properties']['slider']['width'] !== '' ? $field['properties']['slider']['width'] : '100') . '</width>
            </slider>
            ';
        } elseif ($field['type'] === 'Color') {
            $additionlConfig .= '<renderType>colorpicker</renderType>';

            if (is_array($field['properties']['valuePicker']['items'])) {
                $counter = 0;
                $items = '<items type="array">';
                foreach ($field['properties']['valuePicker']['items'] as $key => $value) {
                    $items .= '
                    <numIndex index="' . $counter . '" type="array">
                        <numIndex index="0">' . $value . '</numIndex>
                        <numIndex index="1">' . $key . '</numIndex>
                    </numIndex>';
                    $counter++;
                }
                $items .= '</items>';
            }
        } elseif ($field['type'] === 'Date' || $field['type'] === 'DateTime' || $field['type'] === 'Time') {
            $additionlConfig .= '<renderType>inputDateTime</renderType>';
        }

        if ($field['properties']['displayAge']) {
            $additionlConfig .= '<displayAge>true</displayAge>';
        }

        if (isset($field['properties']['default']) && $field['properties']['default'] === '@now') {
            $field['properties']['default'] = strtotime('now');
        }

        return '
        <' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description</description>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '20') . '</size>
                    <max>' . ($field['properties']['max'] > 0 ? $field['properties']['max'] : '700') . '</max>
                    <eval>' . $evalFields . '</eval>
                    <placeholder>' . $field['properties']['placeholder'] . '</placeholder>
                    <default>' . $field['properties']['default'] . '</default>
                    <autocomplete>' . ($field['properties']['autocomplete'] === true ? 'true ' : 'false') . '</autocomplete>
                    ' . $additionlConfig . '
                    ' . $items . '
                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>
        ';
    }

    /**
     * Create an flexform inline field for pictures (Image)
    */
    protected function createImageField(array $field, array $contentBlock): string
    {
        return '
        <' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description</description>
                <config>

                    <type>inline</type>
                    <minitems>' . ($field['properties']['minItems'] ?? '0') . '</minitems>
                    <maxitems>' . ($field['properties']['maxItems'] ?? '9999') . '</maxitems>
                    <eval>' . ($field['properties']['required'] === true ? 'required' : '') . '</eval>
                    <foreign_table>sys_file_reference</foreign_table>
                    <foreign_table_field>tablenames</foreign_table_field>
                    <foreign_label>uid_local</foreign_label>
                    <foreign_sortby>sorting_foreign</foreign_sortby>
                    <foreign_field>uid_foreign</foreign_field>
                    <foreign_selector>uid_local</foreign_selector>
                    <foreign_match_fields>
                        <fieldname>' . $field['_identifier'] . '</fieldname> <!-- This is the field name -->
                    </foreign_match_fields>
                    <appearance type="array">
                        <newRecordLinkAddTitle>1</newRecordLinkAddTitle>
                        <headerThumbnail>
                            <field>uid_local</field>
                            <height>64</height>
                            <width>64</width>
                        </headerThumbnail>
                        <enabledControls>
                            <info>1</info>
                            <new>0</new>
                            <dragdrop>1</dragdrop>
                            <sort>1</sort>
                            <hide>1</hide>
                            <delete>1</delete>
                            <localize>1</localize>
                        </enabledControls>
                        <createNewRelationLinkTitle>LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference</createNewRelationLinkTitle>
                    </appearance>
                    <behaviour>
                        <localizationMode>select</localizationMode>
                        <localizeChildrenAtParentLocalization>1</localizeChildrenAtParentLocalization>
                    </behaviour>
                    <overrideChildTca>
                        <columns type="array">
                            <uid_local type="array">
                                <config type="array">
                                    <appearance type="array">
                                        <elementBrowserType>file</elementBrowserType>
                                        <elementBrowserAllowed>jpg,png,svg,jpeg,gif</elementBrowserAllowed>
                                    </appearance>
                                </config>
                            </uid_local>
                        </columns>
                        <types type="array">
                            <numIndex index="' . File::FILETYPE_UNKNOWN . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                            <numIndex index="' . File::FILETYPE_TEXT . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                            <numIndex index="' . File::FILETYPE_IMAGE . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                        </types>
                    </overrideChildTca>
                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>
        ';
    }

    /**
     * Create flexform textfields like textarea, rte fields
     */
    protected function createTextarea(array $field, array $contentBlock): string
    {
        return '
        <' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description</description>
                <config>
                    <type>text</type>
                    <cols>' . ($field['properties']['cols'] === true ? $field['properties']['cols'] : '24') . '</cols>
                    <rows>' . ($field['properties']['rows'] === true ? $field['properties']['rows'] : '3') . '</rows>
                    ' . ($field['properties']['enableRichtext'] === true ? '<enableRichtext>true</enableRichtext>' : '') . '
                    ' . (
                strlen(
                    $field['properties']['richtextConfiguration'] . ''
                ) > 0
                ? '<richtextConfiguration>' . $field['properties']['richtextConfiguration'] . '</richtextConfiguration>'
                : ''
            ) . '
                    <eval>' . ($field['properties']['required'] === true ? 'required' : '') . ($field['properties']['trim'] === true && $field['properties']['required'] === true ? ',  ' : '') . ($field['properties']['trim'] === true ? 'trim ' : '') . '</eval>
                    <placeholder>' . $field['properties']['placeholder'] . '</placeholder>
                    <default>' . $field['properties']['default'] . '</default>
                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>
        ';
    }

    /**
     * Create inline field for a collection.
     * It overrides the TCA array directly.
     */
    protected function createCollection(array $field, array $contentBlock): string
    {
        // get the fields in the collections
        $fieldsConfig = '';
        foreach ($field['properties']['fields'] as $collectionField) {
            if ($collectionField['type'] === 'Collection') {
                $fieldsConfig = $fieldsConfig . $this->createCollection($collectionField, $contentBlock);
            } else {
                $fieldsConfig .= $this->createField($collectionField, $contentBlock);
            }
        }

        $GLOBALS['TCA']['tx_contentblocks_reg_api_collection']['columns'][Constants::FLEXFORM_FIELD]
        ['config']['ds']
        [$this->dataService->uniqueCombinedIdentifier($contentBlock['CType'], $field['_identifier'])] = '<T3DataStructure>
                <meta>
                    <langDisable>1</langDisable>
                </meta>
                <sheets>
                    <sDEF>
                        <ROOT>
                            <TCEforms>
                                <sheetTitle>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                                . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</sheetTitle>
                            </TCEforms>
                            <type>array</type>
                            <el>
                                ' . $fieldsConfig . '
                            </el>
                        </ROOT>
                    </sDEF>
                </sheets>
            </T3DataStructure>';

        return '<' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <config>
                    <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                    . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                    <type>inline</type>
                    <foreign_table>tx_contentblocks_reg_api_collection</foreign_table>
                    <foreign_field>content_block_foreign_field</foreign_field>
                    <foreign_table_field>content_block_foreign_table_field</foreign_table_field>
                    <foreign_match_fields>
                        <content_block_field_identifier>'
            . $this->dataService->uniqueCombinedIdentifier($contentBlock['CType'], $field['_identifier'])
            . '</content_block_field_identifier>
                    </foreign_match_fields>'
            . (
                ($field['properties']['minItems'] ?? false)
                ? '<minitems>' . $field['properties']['minItems'] . '</minitems>'
                : ''
            )
            . (
                ($field['properties']['maxItems'] ?? false)
                ? '<maxitems>' . $field['properties']['maxItems'] . '</maxitems>'
                : ''
            )
            . '
                    <appearance type="array">
                        <enabledControls type="array">
                            <delete>1</delete>
                            <dragdrop>1</dragdrop>
                            <new>1</new>
                            <hide>1</hide>
                            <info>1</info>
                            <localize>1</localize>
                        </enabledControls>
                        <useSortable>1</useSortable>
                    </appearance>

                    <overrideChildTca>
                        <columns type="array">
                            <content_block_field_identifier type="array">
                                <label>Do not touch!</label>
                                <config type="array">
                                    <default>'
            . $this->dataService->uniqueCombinedIdentifier($contentBlock['CType'], $field['_identifier'])
            . '</default>
                                </config>
                            </content_block_field_identifier>
                            <content_block type="array">
                                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                                . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                            </content_block>
                        </columns>
                    </overrideChildTca>

                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>';
    }

    /**
     * Create selection, checkboxes, singel select, radio boxes, multi select side by side, toggles.
     */
    protected function createSelections($field, $contentBlock)
    {
        if (is_array($field['properties']['items'])) {
            $counter = 0;
            $items = '<items type="array">';
            foreach ($field['properties']['items'] as $key => $value) {
                $items .= '
                <numIndex index="' . $counter . '" type="array">
                    <numIndex index="0">' . $value . '</numIndex>
                    <numIndex index="1">' . $key . '</numIndex>
                </numIndex>';
                $counter++;
            }
            $items .= '</items>';
        }

        $type = 'select';
        if ($field['type'] === 'Checkbox' || $field['type'] === 'Toggle') {
            $type = 'check';
        }
        if ($field['type'] === 'Radiobox') {
            $type = 'radio';
        }

        $additionlConfig = '';
        if ($field['type'] === 'Select') {
            $additionlConfig = '<renderType>selectSingle</renderType>';
        }
        if ($field['type'] === 'MultiSelect') {
            $additionlConfig = '<renderType>selectMultipleSideBySide</renderType>';
        }
        if ($field['type'] === 'Toggle') {
            $additionlConfig = '<renderType>checkboxToggle</renderType>';
        }

        if ($field['properties']['cols']) {
            $additionlConfig .= '<cols>' . $field['properties']['cols'] . '</cols>';
        }
        if ($field['properties']['maxItems']) {
            $additionlConfig .= '<maxitems>' . $field['properties']['maxItems'] . '</maxitems>';
        }
        if ($field['properties']['minItems']) {
            $additionlConfig .= '<minitems>' . $field['properties']['minItems'] . '</minitems>';
        }

        return '
        <' . $field['_identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description</description>
                <config>
                    <type>' . $type . '</type>
                    ' . ($field['properties']['required'] === true ? '<eval>required</eval>' : '') . '
                    <default>' . $field['properties']['default'] . '</default>
                    ' . $items . '
                    ' . $additionlConfig . '
                </config>
            </TCEforms>
        </' . $field['_identifier'] . '>
        ';
    }

    /**
     * Public function to create flexform settings.
     * Register the flexform directly to the TCA array.
     */
    public function createFlexform(array $contentBlock): void
    {
        $flexFormFieldsConfig = '';

        foreach ($contentBlock['yaml']['fields'] as $field) {
            $flexFormFieldsConfig .= $this->createField($field, $contentBlock);
        }

        /***************
         * Add flexForms for content element configuration
         */
        $GLOBALS['TCA']['tt_content']['columns'][Constants::FLEXFORM_FIELD]['config']['ds'][$contentBlock['CType']] = $this->flexFormTemplate($flexFormFieldsConfig);
    }

    /**
     * Basic generate Flexform wraping structure surrounding.
     */
    protected function flexFormTemplate(string $fieldConfig): string
    {
        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
            <T3DataStructure>
                <meta>
                    <langDisable>1</langDisable>
                </meta>
                <sheets>
                    <sDEF>
                        <ROOT>
                            <TCEforms>
                                <sheetTitle>FLEX FORM Text</sheetTitle>
                            </TCEforms>
                            <type>array</type>
                            <el>
            ' . $fieldConfig . '
                            </el>
                        </ROOT>
                    </sDEF>
                </sheets>
            </T3DataStructure>
            ';
    }

    /**
     * Parse every single field.
     * Make a decision which method should be used. Mapping the symphony types and special types to what it is in flexform.
     */
    protected function createField(array $field, array $contentBlock): string
    {
        if (!is_array($field)) {
            return '';
        } // if no array given, return
        if ($field['type'] === 'Collection') {
            return $this->createCollection($field, $contentBlock);
        }
        switch ($field['type']) {
            case 'Email':
            case 'Integer':
            case 'Money':
            case 'Number':
            case 'Percent':
            case 'Text':
            case 'Password':
            case 'Range':
            case 'Tel':
            case 'Color':
            case 'Date':
            case 'DateTime':
            case 'Time':
                return $this->createInputField($field, $contentBlock);
            case 'Textarea':
            case 'TextMultiline':
                return $this->createTextarea($field, $contentBlock);
            case 'Link':
            case 'Url':
                return $this->createTypoLink($field, $contentBlock);
            case 'Image':
                return $this->createImageField($field, $contentBlock);
            case 'Select':
            case 'Checkbox':
            case 'MultiSelect':
            case 'Radiobox':
            case 'Toggle':
                return $this->createSelections($field, $contentBlock);
            default:
                return '';
        }
    }
}
