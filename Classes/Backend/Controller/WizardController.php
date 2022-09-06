<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Backend\Controller;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\DatabaseService;

/**
 * This file is part of the "Content Block Registration API" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *
 **/

/**
 * Wizard
 */
class WizardController extends ActionController
{
    /**
     * Start viw of content blocks kickstarter
     */
    public function newAction()
    {
        // Nothing to
    }

    /**
     * Creates a new content block
     *
     * @var string $contentBlocks
     */
    public function createAction(string $contentBlocks)
    {
        $contentBlockFromWizard = $contentBlocks;
        // $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        // $this->redirect('new');
        if (strlen($contentBlockFromWizard) < 10) {
            $this->addFlashMessage('I have not written anything, because there was no configuration.', '', AbstractMessage::ERROR);
            $this->redirect('new');
        }

        // create the content block
        $contentBlock = json_decode($contentBlockFromWizard, true);

        if (count($contentBlock) < 3) {
            $this->addFlashMessage('I have not written anything, because there was no configuration. No field given.', '', AbstractMessage::ERROR);
            $this->redirect('new');
        }
        if (strlen($contentBlock['packageName']) < 1) {
            $this->addFlashMessage('I have not written anything, because there was no configuration. No package name given.', '', AbstractMessage::ERROR);
            $this->redirect('new');
        }

        // make sure, package name is lowercase
        $contentBlock['packageName'] = strtolower($contentBlock['packageName']);

        //TODO
        $contentBlock['vendor'] = 'typo3-contentblocks';

        $cbBasePath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . Constants::BASEPATH . $contentBlock['packageName'];

        /***  check if directory exists, if so, stop. */
        if (is_dir($cbBasePath)) {
            $this->addFlashMessage('I have not written anything, because the content block seems to allready exists.', '', AbstractMessage::ERROR);
            $this->redirect('new');
        }

        /* create directory */
        mkdir($cbBasePath);
        $cbBasePath .= '/';
        mkdir($cbBasePath . 'dist');
        mkdir($cbBasePath . 'src/Language', 0777, true);

        // remember fields identifier
        $fieldIdentifierList = [];
        $fieldsForTemplate = "\n";
        $fieldsForXLF = '';

        /* +++++  EditorInterface.yaml  +++++ */

        // $editorInterfaceYaml = $contentBlock;
        $editorInterfaceYaml ['group'] = 'common';
        $editorInterfaceYaml ['fields'] = [];
        foreach ($contentBlock as $field => $value) {
            if ($field === 'packageName') {
                continue;
            }
            if ($field === 'backendName') {
                continue;
            }
            if (!is_array($value)) {
                continue;
            }
            $tempField = [];

            $tempField['identifier'] = $field;
            $fieldIdentifierList[] = $field;

            if ($value['type'] === 'Image' && (int)($value['properties']['maxItems']) == 1) {
                $fieldsForTemplate .= '            <f:image image="{' . $field . '}" />' . "\n";
            } elseif ($value['type'] === 'Image' && (int)($value['properties']['maxItems']) != 1) {
                $fieldsForTemplate .= "\n";
                $fieldsForTemplate .= '            <f:for each="{' . $field . '}" as="i">' . "\n";
                $fieldsForTemplate .= '                <f:image image="{i}" />' . "\n";
                $fieldsForTemplate .= '            </f:for>' . "\n";
            } elseif ($value['type'] === 'Textarea' && $value['properties']['enableRichtext']) {
                $fieldsForTemplate .= '            <f:format.html parseFuncTSPath="lib.parseFunc_RTE">{' . $field . '}</f:format.html>' . "\n";
            } else {
                $fieldsForTemplate .= '            <p>{' . $field . '}</p>' . "\n";
            }

            $tempField['type'] = $value['type'];
            $tempField['properties'] = [];

            foreach ($value['properties'] as $property => $propertyVal) {
                if ($property === 'translationLabel') {
                    $fieldsForXLF .= "\n";
                    $fieldsForXLF .= '            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.' . $tempField['identifier'] . '.label" xml:space="preserve">' . "\n";
                    $fieldsForXLF .= '                <source>' . $propertyVal . '</source>' . "\n";
                    $fieldsForXLF .= '            </trans-unit>' . "\n";
                    continue;
                }
                if ($property === 'translationDescription') {
                    $fieldsForXLF .= "\n";
                    $fieldsForXLF .= '            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.' . $tempField['identifier'] . '.description" xml:space="preserve">' . "\n";
                    $fieldsForXLF .= '                <source>' . $propertyVal . '</source>' . "\n";
                    $fieldsForXLF .= '            </trans-unit>' . "\n";
                    continue;
                }

                if ($property === 'items') {
                    $tempItemsArrExploded = \explode(PHP_EOL, $propertyVal);
                    foreach ($tempItemsArrExploded as $tempItem => $value) {
                        $tempItem = \explode(':', $value);
                        $tempField['properties'][$property][$tempItem[0]] = $tempItem[1];
                    }
                } else {
                    $tempField['properties'][$property] = $propertyVal;
                }
            }

            // $value['identifier'] = $field;
            $editorInterfaceYaml['fields'][] = $tempField;
        }
        file_put_contents($cbBasePath . 'EditorInterface.yaml', Yaml::dump($editorInterfaceYaml, 10));

        /* Create files */
        file_put_contents($cbBasePath . 'dist/EditorPreview.css', '/* Created by Content Block Wizard */');
        file_put_contents($cbBasePath . 'dist/Frontend.css', '/* Created by Content Block Wizard */');
        file_put_contents($cbBasePath . 'dist/Frontend.js', '/* Created by Content Block Wizard */');

        /* +++++  EditorPreview.html  +++++ */
        // TODO: Render template
        $editorPreviewTemplate = '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:be="http://typo3.org/ns/TYPO3/CMS/Backend/ViewHelpers" data-namespace-typo3-fluid="true">' . "\n";
        $editorPreviewTemplate .= '    <f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '-be" href="CB:' . $contentBlock['packageName'] . '/dist/EditorPreview.css"/>' . "\n";
        $editorPreviewTemplate .= "\n";
        $editorPreviewTemplate .= '    <be:link.editRecord uid="{data.uid}" table="tt_content" id="element-tt_content-{data.uid}">' . "\n";
        $editorPreviewTemplate .= '        <div class="' . $contentBlock['packageName'] . '">' . "\n";
        $editorPreviewTemplate .= $fieldsForTemplate . "\n";
        $editorPreviewTemplate .= '        </div>' . "\n";
        $editorPreviewTemplate .= '    </be:link.editRecord>' . "\n";
        $editorPreviewTemplate .= '</html>' . "\n";

        file_put_contents(
            $cbBasePath . 'src/EditorPreview.html',
            $editorPreviewTemplate
        );

        /* +++++  Frontend.html  +++++ */
        // TODO: Render template
        $frontendTemplate = '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">' . "\n";
        $frontendTemplate .= "\n";
        $frontendTemplate .= '    <f:layout name="Default" />' . "\n";
        $frontendTemplate .= "\n";
        $frontendTemplate .= '    <f:section name="Main">' . "\n";
        $frontendTemplate .= "\n";
        $frontendTemplate .= '        <f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '-be" href="CB:' . $contentBlock['packageName'] . '/dist/EditorPreview.css"/>' . "\n";
        $frontendTemplate .= '        <f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '" href="CB:' . $contentBlock['packageName'] . '/dist/Frontend.css"/>' . "\n";
        $frontendTemplate .= '        <f:asset.script identifier="content-block-' . $contentBlock['packageName'] . '" src="CB:' . $contentBlock['packageName'] . '/dist/Frontend.js"/>' . "\n";
        $frontendTemplate .= "\n";
        $frontendTemplate .= '        <div class="' . $contentBlock['packageName'] . '">' . "\n";
        $frontendTemplate .= $fieldsForTemplate . "\n";
        $frontendTemplate .= '        </div>' . "\n";
        $frontendTemplate .= "\n";
        $frontendTemplate .= '    </f:section>' . "\n";
        $frontendTemplate .= '</html>' . "\n";

        file_put_contents(
            $cbBasePath . 'src/Frontend.html',
            $frontendTemplate
        );

        /* +++++  Default.xlf  +++++ */
        $xmlOutput = '<?xml version="1.0"?>' . "\n";
        $xmlOutput .= '<xliff version="1.0">' . "\n";
        $xmlOutput .= '    <file datatype="plaintext"' . "\n";
        $xmlOutput .= '            original="messages"' . "\n";
        $xmlOutput .= '            source-language="en"' . "\n";
        $xmlOutput .= '            product-name="text">' . "\n";
        $xmlOutput .= '        <header/>' . "\n";
        $xmlOutput .= '        <body>' . "\n";
        $xmlOutput .= '            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.title" xml:space="preserve">' . "\n";
        $xmlOutput .= '                <source>' . $contentBlock['backendName'] . '</source>' . "\n";
        $xmlOutput .= '            </trans-unit>' . "\n";
        $xmlOutput .= '            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.description" xml:space="preserve">' . "\n";
        $xmlOutput .= '                <source>' . $contentBlock['backendName'] . ' - ' . $contentBlock['packageName'] . ' created by Content Block Builder.</source>' . "\n";
        $xmlOutput .= '            </trans-unit>' . "\n";
        $xmlOutput .= $fieldsForXLF . "\n";
        $xmlOutput .= '        </body>' . "\n";
        $xmlOutput .= '    </file>' . "\n";
        $xmlOutput .= '</xliff>' . "\n";

        file_put_contents(
            $cbBasePath . 'src/Language/Default.xlf',
            $xmlOutput
        );

        // TODO: Fetch SVG from file
        file_put_contents(
            $cbBasePath . 'ContentBlockIcon.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg>'
        );

        /* write composer.json */
        $composerJson = [
            'name' => 'typo3-contentblocks/' . $contentBlock['packageName'],
            'description' => 'Content block created by Content Block Builder.',
            'type' => 'typo3-contentblock',
            'license' => 'GPL-2.0-or-later',
            'authors' => [['name' => 'Structured Content Initiative']],
            'require' => ['typo3-contentblocks/contentblocks-reg-api' => '*'],
        ];
        file_put_contents(
            $cbBasePath . 'composer.json',
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        $this->addFlashMessage('The content block was created.', '', AbstractMessage::OK);
        $this->addFlashMessage('Please clear all caches bevor you use the new content block.', '', AbstractMessage::WARNING);

        // Update database
        $updateResult = GeneralUtility::makeInstance(DatabaseService::class)->addColumnsToCType($editorInterfaceYaml ['fields'], $contentBlock['packageName']);
        if ($updateResult === true) {
            $this->addFlashMessage('New columns created at the database.', '', AbstractMessage::OK);
        } elseif (is_array($updateResult) && isset($updateResult['error'])) {
            $this->addFlashMessage(
                'An error occured while trying to update database: ' . $updateResult['error'],
                '',
                AbstractMessage::ERROR
            );
        } else {
            $this->addFlashMessage(
                'Could not update database. You must compare and update database to add your new columns there.',
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect('new');
    }
}
