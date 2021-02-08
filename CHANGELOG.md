# Croox Asset Cleaner Loader

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 0.1.0 - 2021-02-08
Support to load CSS via JS

### Added
- Support to load CSS via JS
- PHP Api filter acll_loader_style_handles
- Api function acll_loader.loadAssetsByType
- Api function acll_loader.loadAssets
- Finally remove assets from print_scripts_array and print_styles_array, if still somehow in queue

### Removed
- Api function acll_loader.loadScripts, use acll_loader.loadAssetsByType instead

## 0.0.2 - 2021-02-07
Fix

### Removed
- Function acll_is_rest

## 0.0.1 - 2021-02-07
Cleans assets and loads JS ... no CSS loading by now

### Added
- Cleaner
- Loader and acll_loader script. (Only JS by now)
