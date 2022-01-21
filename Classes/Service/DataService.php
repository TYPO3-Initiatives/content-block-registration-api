<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use TYPO3\CMS\Core\SingletonInterface;

class DataService implements SingletonInterface
{
    /**
     * @param array $data
     * @param array $path
     * @return mixed|null
     */
    public function extractData(array $data, array $path)
    {
        $data = $data[array_shift($path)] ?? null;
        if (empty($data)) {
            return null;
        }
        if (empty($path)) {
            return $data;
        }
        return $this->extractData($data, $path);
    }

    public function setData(array &$data, array $path, array $value): void
    {
        $currentRef = &$data;
        while (!empty($path)) {
            $next = array_shift($path);
            $currentRef = &$currentRef[$next];
        }
        $currentRef = $value;
    }

    public function combinedIdentifierToArray(string $combinedIdentifier): array
    {
        return explode('.', $combinedIdentifier);
    }

    public function arrayToCombinedIdentifier(array $path): string
    {
        return implode('.', $path);
    }

    public function uniqueCombinedIdentifier(string $cType, string $combinedIdentifier): string
    {
        return $cType . '|' . $combinedIdentifier;
    }

    public function splitUniqueCombinedIdentifier($uniqueCombinedIdentifier): array
    {
        return explode('|', $uniqueCombinedIdentifier);
    }

    /**
     * Manage to have SQL compatible column names, prefixed with "cb_".
     * Result: cb_content_blockidentifier_column_path_column_name
     */
    public function uniqueColumnName(string $cType, string $combinedIdentifier): string
    {
        return 'cb_' . str_replace('-', '_', $cType) . '_' . str_replace('-', '_', str_replace('.', '_', $combinedIdentifier));
    }
}
