<?php

declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Service;

use Sci\SciApi\Constants;
use Sci\SciApi\Validator\ContentBlockValidator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService
{
    public static function configuration(): array
    {
        return self::configurationUncached();

        // TODO
        //        $cache = GeneralUtility::makeInstance(CacheManager::class)
        //            ->getCache(Constants::CACHE);
        //
        //        if (false === $configuration = $cache->get(Constants::CACHE_CONFIGURATION_ENTRY)) {
        //            $configuration = self::configurationUncached();
        //            $cache->set(Constants::CACHE_CONFIGURATION_ENTRY, $configuration, [], 0);
        //        }
        //
        //        return $configuration;
    }

    public static function contentBlockConfiguration(string $cType): ?array
    {
        return self::configuration()[$cType] ?? null;
    }

    protected static function configurationUncached(): array
    {
        $hostBasePath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . Constants::BASEPATH;

        // create dir if not existent
        GeneralUtility::mkdir_deep($hostBasePath);

        $cbsFinder = new Finder();
        $cbsFinder->directories()->in($hostBasePath);

        $contentBlockConfiguration = [];
        foreach ($cbsFinder as $cbDir) {
            $_cbConfiguration = self::configurationForContentBlockByPath($cbDir);

            $contentBlockConfiguration [$_cbConfiguration['CType']] = $_cbConfiguration;
        }

        return $contentBlockConfiguration;
    }

    protected static function configurationForContentBlockByPath(SplFileInfo $splPath): array
    {
        // directory paths (full)
        $realPath = $splPath->getPathname() . DIRECTORY_SEPARATOR;
        $languageRealPath = $realPath . 'src' . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR;

        // directory paths (relative to publicPath())
        $path = Constants::BASEPATH . $splPath->getBasename() . DIRECTORY_SEPARATOR;
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
        $frontendTemplatePath = $path . 'src';

        // relation fields
        $relationFields = [];
        foreach ($editorInterface['fields'] ?? [] as $field) {
            if (in_array($field['type'] ?? '', ['Icon', 'Image'])) {
                $relationFields[] = $field['identifier'];
            }
        }

        $cbConfiguration = [
            'vendor' => $vendor,
            'package' => $packageName,
            'path' => $path,
            'srcPath' => $srcPath,
            'distPath' => $distPath,
            'icon' => $iconPath,
            'iconProviderClass' => $iconProviderClass,
            'CType' => $cType,
            'relationFields' => $relationFields,
            'frontendTemplatePath' => $frontendTemplatePath,
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
}
