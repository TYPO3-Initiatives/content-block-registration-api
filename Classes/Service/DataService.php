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
        $current = &$data;
        while (!empty($path)) {
            $next = array_shift($path);
//            // do not set paths with non-existing parents
//            if (!isset($current[$next])) {
//                return;
//            }
            $current = &$current[$next];
        }
        $current = $value;
    }

    public function combinedIdentifierToArray(string $combinedIdentifier): array
    {
        return explode('.', $combinedIdentifier);
    }

    public function arrayToCombinedIdentifier(array $path): string
    {
        return implode('.', $path);
    }

    public function uniqueCombinedIdentifier(string $cType, string $combinedIdentifier): string {
        return $cType . '|' . $combinedIdentifier;
    }

    public function splitUniqueCombinedIdentifier($uniqueCombinedIdentifier): array {
        return explode('|', $uniqueCombinedIdentifier);
    }
}
