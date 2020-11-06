# Registration API for Content Blocks

## Introduction

This API provides an easy and reliable way to register content blocks (composer packages).
See [Content Blocks Registration in TYPO3](https://github.com/TYPO3-Initiatives/structured-content/blob/master/Documentation/ContentBlocks/ContentBlockRegistration.md)
 for more information about content blocks.

## Requirements
* TYPO3 v10+
* needs Fluid based Page (which is default in v10 and will be the only one any ways in v11) module for backend preview

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

### Storage

The content block composer package it is stored in `typo3conf/contentBlocks/`.

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
