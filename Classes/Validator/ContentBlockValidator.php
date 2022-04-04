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
            !file_exists($cbPath . 'composer.json')
            || !file_exists($cbPath . 'EditorInterface.yaml')
        ) {
            return false;
        }

        return true;
    }
}
