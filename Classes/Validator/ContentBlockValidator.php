<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Validator;

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
            throw new \Exception(sprintf('composer.json not found in ContentBlock %s', $cbPath));
        }

        // is there a EditorInterface.yaml?
        if (!file_exists($cbPath . 'EditorInterface.yaml')) {
            throw new \Exception(sprintf('EditorInterface.yaml not found in ContentBlock %s', $cbPath));
        }

        // Is there a ContentBlockIcon?
        if (
            !file_exists($cbPath . 'ContentBlockIcon.svg')
            && !file_exists($cbPath . 'ContentBlockIcon.png')
            && !file_exists($cbPath . 'ContentBlockIcon.gif')
        ) {
            throw new \Exception(sprintf('ContentBlockIcon.(svg|png|gif) not found in ContentBlock %s', $cbPath));
        }

        // check composer type
        $composerJson = json_decode(file_get_contents($cbPath . 'composer.json'), true);
        // If there is an old content block with type of 'typo3-cms-contentblock'?
        if ($composerJson['type'] === 'typo3-cms-contentblock') {
            throw new \Exception(sprintf('Your ContentBlock %s is of old type \'typo3-cms-contentblock\'. You must migrate the composer type to \'typo3-contentblock\' in your composer.json.', $cbPath));
        }
        // Is the ContentBlock of type 'typo3-contentblock'?
        if ($composerJson['type'] !== 'typo3-contentblock') {
            throw new \Exception(sprintf('Your ContentBlock must be of composer type \'typo3-contentblock\' in %s', $cbPath));
        }

        // Is there a translation file?
        if (
            !file_exists($cbPath . 'src/Language/Default.xlf')
            && !file_exists($cbPath . 'src/Language/EditorInterface.xlf')
        ) {
            throw new \Exception(sprintf('ContentBlock translation for backend is missing. No \'src/Language/Default.xlf\' and no \'src/Language/EditorInterface.xlf\' found in ContentBlock %s', $cbPath));
        }

        return true;
    }
}
