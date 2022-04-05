<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Validator;

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;

class ContentBlockValidator implements SingletonInterface
{
    /**
     * Throws on validation error
     * @param array $cbConfiguration
     */
    public function validate(array $cbConfiguration): void
    {
        // TODO
        // we should check that there are no fields 'data' or 'cb' defined since these would clash
        // identifiers should not contain '.'
    }

    /**
     * Checks if there are the needed files to prevent for errors.
     *
     * Throws on validation error
     *
     * @throws \Exception
     * @param string $cbConfiguration
     */
    public function validateCbPathStructure(string $cbPath): bool
    {
        // TODO
        // we should check that there are no fields 'data' or 'cb' defined since these would clash
        // identifiers should not contain '.'
        // Throw a error
        if (substr($cbPath, -1) !== '/') {
            $cbPath .= '/';
        }

        // is there a composer.json?
        if (!file_exists($cbPath . 'composer.json')) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                return false;
            }
            throw new \Exception(sprintf('composer.json not found in ContentBlock %s', $cbPath));
        }

        // is there a EditorInterface.yaml?
        if (!file_exists($cbPath . 'EditorInterface.yaml')) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                return false;
            }
            throw new \Exception(sprintf('EditorInterface.yaml not found in ContentBlock %s', $cbPath));
        }

        // Is there a ContentBlockIcon?
        if (
            !file_exists($cbPath . 'ContentBlockIcon.svg')
            && !file_exists($cbPath . 'ContentBlockIcon.png')
            && !file_exists($cbPath . 'ContentBlockIcon.gif')
        ) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                return false;
            }
            throw new \Exception(sprintf('ContentBlockIcon.(svg|png|gif) not found in ContentBlock %s', $cbPath));
        }

        // Is the ContentBlock of type 'typo3-cms-contentblock'?
        if (
            strpos(file_get_contents($cbPath . 'composer.json'), '"type": "typo3-cms-contentblock"') === false
            && strpos(file_get_contents($cbPath . 'composer.json'), "'type': 'typo3-cms-contentblock'") === false
        ) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                return false;
            }
            throw new \Exception(sprintf('Your ContentBlock must be of composer type \'typo3-cms-contentblock\' in %s', $cbPath));
        }

        // Is there a translation file?
        if (
            !file_exists($cbPath . 'src/Language/Default.xlf')
            && !file_exists($cbPath . 'src/Language/EditorInterface.xlf')
        ) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                return false;
            }
            throw new \Exception(sprintf('ContentBlock translation for backend is missing. No \'src/Language/Default.xlf\' and no \'src/Language/EditorInterface.xlf\' found in ContentBlock %s', $cbPath));
        }

        return true;
    }
}
