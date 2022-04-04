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
        if (
            !file_exists($cbPath . 'composer.json') // is there a composer.json?
            || !file_exists($cbPath . 'EditorInterface.yaml') // is there a EditorInterface.yaml?
            || (    // Is there a ContentBlockIcon?
                    !file_exists($cbPath . 'ContentBlockIcon.svg')
                    && !file_exists($cbPath . 'ContentBlockIcon.png')
                    && !file_exists($cbPath . 'ContentBlockIcon.gif')
                )
            || (    // Is the ContentBlock of type 'typo3-cms-contentblock'?
                    strpos(file_get_contents($cbPath . 'composer.json'), '"type": "typo3-cms-contentblock"') === false
                    && strpos(file_get_contents($cbPath . 'composer.json'), "'type': 'typo3-cms-contentblock'") === false
                )
            || (    // Is there a translation file?
                    !file_exists($cbPath . 'src/Language/Default.xlf')
                    && !file_exists($cbPath . 'src/Language/EditorInterface.xlf')
                )
        ) {
            return false;
        }

        return true;
    }
}
