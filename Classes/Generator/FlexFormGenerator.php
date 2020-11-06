<?php
declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Generator;

class FlexFormGenerator
{

    /** create typolink */
    public static function createTypoLink($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size']  > 0 ? $field['properties']['size'] : '30') . '</size>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                    <softref>typolink,typolink_tag,images,url</softref>
                    <wizards>
                        <_PADDING>2</_PADDING>
                        <link>
                            <type>popup</type>
                            <title>Link</title>
                            <module>
                                <name>wizard_element_browser</name>
                                <urlParameters>
                                    <mode>wizard</mode>
                                </urlParameters>
                            </module>
                            <icon>link_popup.gif</icon>
                            <script>browse_links.php?mode=wizard</script>
                            <params>
                                <!--<blindLinkOptions>page,file,folder,url,spec</blindLinkOptions>-->
                            </params>
                            <JSopenParams>height=500,width=500,status=0,menubar=0,scrollbars=1</JSopenParams>
                        </link>
                    </wizards>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    public static function createInputField($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '20') . '</size>
                    <max>' . ($field['properties']['max']  > 0 ? $field['properties']['max'] : '700') . '</max>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    public static function createTextarea($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>text</type>
                    <cols>' . ($field['properties']['cols'] === true ? $field['properties']['cols'] : '24') . '</cols>
                    <rows>' . ($field['properties']['rows'] === true ? $field['properties']['rows'] : '3') . '</rows>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** generate Flexform wrapping structure */
    public static function flexFormTemplate($fieldConfig)
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
}