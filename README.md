[![CGL & unit tests](https://github.com/TYPO3-Initiatives/content-block-registration-api/workflows/CGL%20&%20unit%20tests/badge.svg?branch=master)](https://github.com/TYPO3-Initiatives/content-block-registration-api/actions)
[![Latest Stable Version](https://poser.pugx.org/typo3-contentblocks/contentblocks-reg-api/v)](//packagist.org/packages/typo3-contentblocks/contentblocks-reg-api)
[![Latest Unstable Version](https://poser.pugx.org/typo3-contentblocks/contentblocks-reg-api/v/unstable)](//packagist.org/packages/typo3-contentblocks/contentblocks-reg-api)
[![License](https://poser.pugx.org/typo3-contentblocks/contentblocks-reg-api/license)](//packagist.org/packages/typo3-contentblocks/contentblocks-reg-api)

# Registration API for Content Blocks

## Introduction

This API provides an easy and reliable way to register content blocks (composer packages).
Follow this README for a quick getting started overview.
Find the full [Documentation](https://github.com/TYPO3-Initiatives/content-block-registration-api/blob/master/Documentation/Index.rst) inside this repository.

### Status

`beta` - the main concepts are laid out, the data storage method refactored and tested. We welcome your feedback.
You can reach us in the TYPO3 Slack `#cig-structuredcontent` ❤️.

## Installation

### For developing on this API

This will set up a TYPO3 v10 with ddev and install the API extension.

#### Requirements

* ddev

1. Clone this repository
1. Run `ddev launch /typo3`

The TYPO3 backend user is "admin", password "adminadmin".

It includes example Content Blocks in a local composer repository that are installed by default.

### For using Content Blocks

#### Requirements
* TYPO3 v10+
* In TYPO3 v10, backend previews require the [Fluid based Page module](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html) to be enabled.

The API required to use content blocks consists of 2 composer packages: an API extension and a composer plugin.

#### Installation steps
1. `composer req typo3-contentblocks/contentblocks-reg-api`
1. Activate the extension `contentblocks_reg_api`
1. Add new database fields: (Backend) `Maintenance` › `Analyze Database Structure`

### Getting/Creating new content blocks

#### Via composer

[This is an example repo](https://github.com/TYPO3-Initiatives/content-block-examples) with a content block
<pre>
composer req typo3-contentblocks/call-to-action:dev-master
</pre>

For using custom content blocks in your project we recommend a [local "path" composer repository](https://getcomposer.org/doc/05-repositories.md#path).

#### Wizard

There is a wizard module that kickstarts Content Blocks for you.

#### Create them locally

You can also add a Content Block directory manually to `typo3conf/contentBlocks`.

## Usage

## Processes that happen during content block registration

### Detecting a content block

The detection of content blocks depends on the composer package type.
The custom composer installer then retrieves all packages, which are of type `typo3-contentblock`.

### Validation

Following aspects are mandatory for a content block to be validated successfully:

- An icon for the content block named "ContentBlockIcon" hast to be present in the package root and of type SVG/PNG/GIF
- The file `EditorInterface.yaml` has to be present in the package root and valid
- The backend language file `Default.xml` or `EditorInterface.xlf` has to be present in the `src/Language` folder of the package
- The file `EditorPreview.html ` has to be present in the `src` folder of the package

### Location

Content blocks are stored in or symlinked to `typo3conf/contentBlocks/`.

### Virtual generation of TCA

TCA is virtually generated from the class implementing a content block field type.

### Generation of FlexForm

Based on the fields defined in the `EditorInterface.yaml` a FlexForm for the editing interface of the content block
is generated and stored in `tt_content.content_block`.

### Registration of the content block

* Register icon
* Add TCA entry in CTypes list including the icon
* Add the content block to the NewContentElementWizard
* Add TypoScript to render the content plugin
* Add PageTS for the content block

## Extension configuration

For some reason it might be necessary to inherit content blocks from your own definition. E. g. if you want to inherit content blocks from lib.contentElement. In that case you can extend the default TypoScript as you need.
This is the default Code:

<pre>
lib.contentBlock = FLUIDTEMPLATE
lib.contentBlock {
    layoutRootPaths {
        -5 = EXT:contentblocks_reg_api/Resources/Private/Layouts/
    }

    partialRootPaths {
        0 = EXT:contentblocks_reg_api/Resources/Private/Partials/
    }

    dataProcessing {
        10 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\CbProcessor
        20 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\FlexFormProcessor
    }
}
</pre>

**Attention**: If you change the code, you are responsible for that the code is working. Beware of data processing. Your content block won't work without that.

You can inject your code via the extension settings in the install tool. There you can set the default TypoScript to your specific file like `EXT:sitepackage/Configuration/TypoScript/contentBlock.typoscript`.

The second thing in the extension configuration is to enable the frame pallet in the appearance section. This might be a helpful feature if you are using fluid_styled_content or bootstrap_package.
