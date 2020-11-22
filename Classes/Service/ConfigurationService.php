<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use LogicException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Validator\ContentBlockValidator;

class ConfigurationService
{
    public static function configuration(): array
    {
        try {
            $cache = GeneralUtility::makeInstance(CacheManager::class)
                ->getCache(Constants::CACHE);

            if (false === $configuration = $cache->get(Constants::CACHE_CONFIGURATION_ENTRY)) {
                $configuration = self::configurationUncached();
                $cache->set(Constants::CACHE_CONFIGURATION_ENTRY, $configuration, [], 0);
            }
        } catch (LogicException | NoSuchCacheException $e) { // if unconfigured or in ext_localconf.php
            $configuration = self::configurationUncached();
        }

        return $configuration;
    }

    public static function cbConfiguration(string $cType): ?array
    {
        return self::configuration()[$cType] ?? null;
    }

    protected static function configurationUncached(): array
    {
        $hostBasePath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . Constants::BASEPATH;

        // create dir if not existent
        GeneralUtility::mkdir_deep($hostBasePath);

        $cbsFinder = new Finder();
        $cbsFinder->directories()->depth('== 0')->in($hostBasePath);

        $contentBlockConfiguration = [];
        foreach ($cbsFinder as $cbDir) {
            $_cbConfiguration = self::byPath($cbDir);

            $contentBlockConfiguration [$_cbConfiguration['CType']] = $_cbConfiguration;
        }
        return $contentBlockConfiguration;
    }

    /**
     * @param SplFileInfo $splPath
     * @return array<string, mixed>
     * @throws \Exception
     */
    protected static function byPath(SplFileInfo $splPath): array
    {
        $cbKey = $splPath->getBasename();

        // directory paths (full)
        $realPath = $splPath->getPathname() . DIRECTORY_SEPARATOR;
        $languageRealPath = $realPath . 'src' . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR;

        // directory paths (relative to publicPath())
        $path = Constants::BASEPATH . $cbKey . DIRECTORY_SEPARATOR;
        $srcPath = $path . 'src' . DIRECTORY_SEPARATOR;
        $distPath = $path . 'dist' . DIRECTORY_SEPARATOR;
        $languagePath = $srcPath . 'Language' . DIRECTORY_SEPARATOR;

        // file paths
        $composerJsonPath = $realPath . 'composer.json';
        $editorInterfaceYamlPath = $realPath . 'EditorInterface.yaml';

        // composer.json
        if (!is_readable($composerJsonPath)) {
            $composerJson = null;
        } else {
            $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        }

        // CType
        if (null === $composerJson) {
            // fallback: use directory name
            $vendor = 'cb_noVendor';
            $packageName = $splPath->getBasename();
        } else {
            [$vendor, $packageName] = explode('/', $composerJson['name']);
        }
        $cType = $vendor . '_' . $packageName;

        // EditorInterface.yaml
        if (!is_readable($editorInterfaceYamlPath)) {
            throw new \Exception(sprintf('%s not found', $editorInterfaceYamlPath));
        }
        $editorInterface = Yaml::parseFile($editorInterfaceYamlPath);
        if (!is_array($editorInterface['fields'])) {
            throw new \Exception(sprintf('Key ‹fields› must be an array in %s', $editorInterfaceYamlPath));
        }

        // add combined '_identifier' and '_parents'
        self::_addFieldIdentifiers($editorInterface['fields']);

        // .xlf
        $editorInterfaceXlf = is_readable($languageRealPath . 'Default.xlf')
            ? $languagePath . 'Default.xlf'
            : $languagePath . 'EditorInterface.xlf';
        if (!is_readable($editorInterfaceYamlPath)) {
            $editorInterfaceXlf = false;
        }

        $frontendXlf = is_readable($languageRealPath . 'Default.xlf')
            ? $languagePath . 'Default.xlf'
            : $languagePath . 'Frontend.xlf';
        if (!is_readable($editorInterfaceYamlPath)) {
            $frontendXlf = false;
        }

        // icon
        $iconPath = null;
        $iconProviderClass = null;
        foreach (['svg', 'png', 'gif'] as $ext) {
            if (is_readable($realPath . 'ContentBlockIcon.' . $ext)) {
                $iconPath = $path . 'ContentBlockIcon.' . $ext;
                $iconProviderClass = $ext === 'svg'
                    ? SvgIconProvider::class
                    : BitmapIconProvider::class;
                break;
            }
        }
        if ($iconPath === null) {
            throw new \Exception(
                sprintf('No icon found for content block %s', $cType)
            );
        }

        // EditorPreview.html
        $editorPreviewHtml = is_readable(
            $realPath . 'src' . DIRECTORY_SEPARATOR . 'EditorPreview.html'
        )
            ? $realPath . 'src' . DIRECTORY_SEPARATOR . 'EditorPreview.html'
            : false;

        // Frontend.html
        $frontendTemplatesPath = $path . 'src';
        // Partials
        $frontendPartialsPath = $frontendTemplatesPath . DIRECTORY_SEPARATOR . 'Partials';
        // Layouts
        $frontendLayoutsPath = $frontendTemplatesPath . DIRECTORY_SEPARATOR . 'Layouts';

        // file fields
        $fields = self::_fields($editorInterface['fields'] ?? []);

        // file fields
        $fileFields = self::_fieldsByTypes($editorInterface['fields'] ?? [], ['Icon', 'Image']);

        // collection fields
        $collectionFields = self::_fieldsByTypes($editorInterface['fields'] ?? [], ['Collection']);

        $cbConfiguration = [
            '__warning' => 'Contents of this "cb" configuration are not API yet and might change!',
            'vendor' => $vendor,
            'package' => $packageName,
            'key' => $cbKey,
            'path' => $path,
            'srcPath' => $srcPath,
            'distPath' => $distPath,
            'icon' => $iconPath,
            'iconProviderClass' => $iconProviderClass,
            'CType' => $cType,
            'fields' => $fields,
            'collectionFields' => $collectionFields,
            'fileFields' => $fileFields,
            'frontendTemplatesPath' => $frontendTemplatesPath,
            'frontendPartialsPath' => $frontendPartialsPath,
            'frontendLayoutsPath' => $frontendLayoutsPath,
            'EditorPreview.html' => $editorPreviewHtml,
            'EditorInterface.xlf' => $editorInterfaceXlf,
            'EditorLLL' => 'LLL:' . $editorInterfaceXlf . ':' . $vendor . '.' . $packageName,
            'Frontend.xlf' => $frontendXlf,
            'FrontendLLL' => 'LLL:' . $frontendXlf . ':' . $vendor . '.' . $packageName,
            'yaml' => $editorInterface,
        ];

        // validate (throws on error)
        GeneralUtility::makeInstance(ContentBlockValidator::class)
            ->validate($cbConfiguration);

        return $cbConfiguration;
    }

    /**
     * @param string $cType
     * @return array<string, array>
     */
    public static function cbFileFields(string $cType): array
    {
        return self::cbConfiguration($cType)['fileFields'] ?? [];
    }

    /**
     * @param string $cType
     * @return array<string, array>
     */
    public static function cbCollectionFields(string $cType): array
    {
        return self::cbConfiguration($cType)['collectionFields'] ?? [];
    }

    /**
     * @param string $cType
     * @param array $path
     * @return array<string, array>
     */
    public static function cbCollectionFieldsAtPath(string $cType, array $path): array
    {
        return array_filter(
            self::cbCollectionFields($cType),
            function ($e) use ($path) {
                $fieldParentPath = $e['_path'];
                array_pop($fieldParentPath);
                return $fieldParentPath === $path;
            }
        );
    }


    /**
     * @param string $cType
     * @param array $path
     * @return array<string, array>
     */
    public static function cbFileFieldsAtPath(string $cType, array $path): array
    {
        return array_filter(
            self::cbFileFields($cType),
            function ($e) use ($path) {
                $fieldParentPath = $e['_path'];
                array_pop($fieldParentPath);
                return $fieldParentPath === $path;
            }
        );
    }

    /**
     * @param string $cType
     * @return array<string, array<string, mixed>> associative field configurations with field
     * identifier as key
     */
    public static function cbFields(string $cType): array
    {
        return self::_fields(self::cbConfiguration($cType)['yaml']['fields'] ?? []);
    }

    public static function cbField(string $cType, string $fieldIdentifier): ?array
    {
        return self::cbFields($cType)[$fieldIdentifier] ?? null;
    }

    protected static function _addFieldIdentifiers(array &$fields, array $parents = []): void
    {
        foreach ($fields as &$f) {
            $identifier = $parents;
            $identifier[] = $f['identifier'];
            $f['_path'] = $identifier;
            // TODO use DataService
            $f['_identifier'] = implode('.', $identifier);
            if (isset($f['properties']['fields'])) {
                self::_addFieldIdentifiers(
                    $f['properties']['fields'],
                    $identifier
                );
            }
        }
    }

    protected static function _fields(array $fields): array
    {
        $matchingFields = [];
        foreach ($fields as &$f) {
            $matchingFields[$f['_identifier']] = $f;
            if (isset($f['properties']['fields'])) {
                $matchingFields += self::_fields($f['properties']['fields']);
            }
        }
        return $matchingFields;
    }

    protected static function _fieldsByTypes(array $fields, array $types): array
    {
        $matchingFields = [];
        foreach ($fields as &$f) {
            if (in_array($f['type'] ?? '', $types)) {
                $matchingFields[$f['_identifier']] = $f;
            }
            if (isset($f['properties']['fields'])) {
                $matchingFields += self::_fieldsByTypes($f['properties']['fields'], $types);
            }
        }
        return $matchingFields;
    }
}
