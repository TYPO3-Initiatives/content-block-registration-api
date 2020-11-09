# Registration API for Content Blocks

## Introduction

This API provides an easy and reliable way to register content blocks (composer packages).
See [Content Blocks Registration in TYPO3](https://github.com/TYPO3-Initiatives/structured-content/blob/master/Documentation/ContentBlocks/ContentBlockRegistration.md)
 for more information about content blocks.

### Status

`alpha` - the main concepts are laid out but nothing is polished yet. We welcome your feedback.
You can reach us in the TYPO3 Slack `#cig-structuredcontent-contentblockcreation` ❤️.

## Installation

### For developing on this API

This will set up a TYPO3 v10 and install the API extension.

It is a quickstart to explore the feature, too.

1) clone this repository
2) `ddev launch /typo3`

It includes example Content Blocks in a local composer that are installed by default.

### For using Content Blocks

#### Requirements
* TYPO3 v10+
* In TYPO3 v10, backend previews require the [Fluid based Page module](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html) to be enabled.

This installs the API required to use content blocks.
It consists of 2 composer packages: an API extension and a composer plugin.

#### Installation steps
<pre>
composer config repositories.cb-api vcs https://github.com/TYPO3-Initiatives/content-block-registration-api.git
composer config repositories.cb-composer-plugin vcs https://github.com/TYPO3-Initiatives/content-blocks-composer-plugin.git
composer config minimum-stability dev
composer req sci/sci-api:dev-master
</pre>

* Add new database fields: (Backend) `Maintenance` › `Analyze Database Structure`

### Getting/Creating new content blocks

#### Via composer

[This is an example repo](https://github.com/TYPO3-Initiatives/content-block-examples) with a content block
<pre>
composer config repositories.cb-examples vcs https://github.com/TYPO3-Initiatives/content-block-examples.git
composer req sci/call-to-action-example:dev-master
</pre>

#### Wizard

There is a wizard module that kickstarts Content Blocks for you.

#### Create them locally

You can also add a Content Block directory manually to `typo3conf/contentBlocks`.

## Usage

## Processes that happen during content block registration

### Detecting a content block

The detection of content blocks depends on the composer package type.
The custom composer installer then retrieves all packages, which are of type `typo3-cms-contentblock`.

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
