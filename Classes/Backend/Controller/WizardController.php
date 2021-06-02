<?php declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Backend\Controller;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use Typo3Contentblocks\ContentblocksRegApi\Constants;

/***
 *
 * This file is part of the "Content Block Registration API" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *
 ***/
/**
 * Wizard
 */
class WizardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        // Nothing to do
    }

    /**
     * action create
     *
     * @var string $contentBlocks
     * @return void
     */
    public function createAction(string $contentBlocks)
    {
        $contentBlockFromWizard = $contentBlocks;
        // $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        // $this->redirect('new');
        if (strlen($contentBlockFromWizard) < 10) {
            $this->addFlashMessage('I have not written anything, because there was no configuration.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new');
        }

        // create the content block
        $contentBlock = json_decode($contentBlockFromWizard, true);

        if (count($contentBlock) < 3) {
            $this->addFlashMessage('I have not written anything, because there was no configuration. No field given.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new');
        }
        if (strlen($contentBlock['packageName']) < 1) {
            $this->addFlashMessage('I have not written anything, because there was no configuration. No package name given.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new');
        }

        // make shure, package name is lowercase
        $contentBlock['packageName'] = strtolower($contentBlock['packageName']);

        //TODO
        $contentBlock['vendor'] = 'typo3-contentblocks';

        $cbBasePath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . Constants::BASEPATH . $contentBlock['packageName'];

        /***  check if directory exists, if so, stop. */
        if (is_dir($cbBasePath)) {
            $this->addFlashMessage('I have not written anything, because the content block seems to allready exists.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new');
        }

        /* create directory */
        mkdir($cbBasePath);
        $cbBasePath .= '/';
        mkdir($cbBasePath . 'dist');
        mkdir($cbBasePath . 'src/Language', 0777, true);

        // remember fields identifyer
        $fieldIdentifierList = [];
        $fieldsForTemplate = '';
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
            $fieldIdentifierList[] =  $field;

            if ($value['type'] === 'Image' && intval($value["properties"]['maxItems']) == 1 ) {
                $fieldsForTemplate .= '
    <f:image image="{' . $field . '}" />';
            }
            elseif($value['type'] === 'Image' && intval($value["properties"]['maxItems']) != 1 ) {
                $fieldsForTemplate .= '
    <f:for each="{' . $field . '}" as="i">
        <f:image image="{i}" />
    </f:for>';
            }
            elseif ($value['type'] === 'Textarea' && $value["properties"]['enableRichtext'] ) {
                $fieldsForTemplate .= '
      <f:format.html parseFuncTSPath="lib.parseFunc_RTE">{' . $field . '}</f:format.html>';
            }
            else {
                $fieldsForTemplate .= '
    <p>{' . $field . '}</p>';
            }

            $tempField['type'] = $value['type'];
            $tempField['properties'] = [];

            foreach ($value['properties'] as $property => $propertyVal) {
                if ($property === 'translationLabel') {
                    $fieldsForXLF .= '
            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.' . $tempField['identifier'] . '.label" xml:space="preserve">
                <source>' . $propertyVal . '</source>
            </trans-unit>
                    ';
                    continue;
                }
                if ($property === 'translationDescription') {
                    $fieldsForXLF .= '
            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.' . $tempField['identifier'] . '.description" xml:space="preserve">
                <source>' . $propertyVal . '</source>
            </trans-unit>
                    ';
                    continue;
                }

                if ($property === 'items') {
                    $tempItemsArrExploded = \explode(PHP_EOL, $propertyVal);
                    $tempItemsArr = [];
                    foreach ($tempItemsArrExploded as $tempItem => $value) {
                        $tempItem = \explode(':', $value);
                        $tempField['properties'][$property][ $tempItem[0] ] = $tempItem[1];
                    }
                } else {
                    $tempField['properties'][$property] = $propertyVal;
                }
            }

            // $value['identifier'] = $field;
            $editorInterfaceYaml ['fields'][] = $tempField;
        }
        file_put_contents($cbBasePath . 'EditorInterface.yaml', Yaml::dump($editorInterfaceYaml, 10));

        /* Create files */
        file_put_contents($cbBasePath . 'dist/EditorPreview.css', '/* Created by Content Block Wizard */');
        file_put_contents($cbBasePath . 'dist/Frontend.css', '/* Created by Content Block Wizard */');
        file_put_contents($cbBasePath . 'dist/Frontend.js', '/* Created by Content Block Wizard */');

        /* +++++  EditorPreview.html  +++++ */

        file_put_contents($cbBasePath . 'src/EditorPreview.html', '
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '-be" href="CB:' . $contentBlock['packageName'] . '/dist/EditorPreview.css"/>
<div class="' . $contentBlock['packageName'] . '">
    ' . $fieldsForTemplate . '
</div>

</html>
        ');

        /* +++++  Frontend.html  +++++ */

        file_put_contents($cbBasePath . 'src/Frontend.html', '
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<f:layout name="Default" />

<f:section name="Main">

    <f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '-be" href="CB:' . $contentBlock['packageName'] . '/dist/EditorPreview.css"/>
    <f:asset.css identifier="content-block-' . $contentBlock['packageName'] . '" href="CB:' . $contentBlock['packageName'] . '/dist/Frontend.css"/>
    <f:asset.script identifier="content-block-' . $contentBlock['packageName'] . '" src="CB:' . $contentBlock['packageName'] . '/dist/Frontend.js"/>

    <div class="' . $contentBlock['packageName'] . '">
        ' . $fieldsForTemplate . '
    </div>

</f:section>
</html>
        ');

        /* +++++  Default.xlf  +++++ */

        file_put_contents($cbBasePath . 'src/Language/Default.xlf', '<?xml version="1.0"?>
<xliff version="1.0">
    <file datatype="plaintext"
            original="messages"
            source-language="en"
            product-name="text">
        <header/>
        <body>
            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.title" xml:space="preserve">
                <source>' . $contentBlock['backendName'] . '</source>
            </trans-unit>
            <trans-unit id="' . $contentBlock['vendor'] . '.' . $contentBlock['packageName'] . '.description" xml:space="preserve">
                <source>' . $contentBlock['backendName'] . ' - ' . $contentBlock['packageName'] . ' created by Content Block Builder.</source>
            </trans-unit>
' . $fieldsForXLF . '
        </body>
    </file>
</xliff>
        ');

        file_put_contents($cbBasePath . 'ContentBlockIcon.svg', '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg>');

        /* write composer.json */
        $composerJson = [
            'name' => 'typo3-contentblocks/' . $contentBlock['packageName'],
            'description' => 'Content block created by Content Block Builder.',
            'type' => 'typo3-cms-contentblock',
            'license' => 'GPL-2.0-or-later',
            'authors' =>  [ ['name' => 'Structured Content Initiative'] ],
            'require' =>  ['typo3-contentblocks/contentblocks-reg-api' => '*'] ,
        ];
        file_put_contents($cbBasePath . 'composer.json', json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $this->addFlashMessage('The content block was created.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        $this->addFlashMessage('Please clear all caches bevor you use the new content block.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

        $this->redirect('new');
    }
}
