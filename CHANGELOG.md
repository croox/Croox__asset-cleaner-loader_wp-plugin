# Croox Asset Cleaner Loader
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 0.4.3 - 2023-08-25
Updated dependencies

### Changed
- Updated to generator-wp-dev-env#1.6.7 ( wp-dev-env-grunt#1.6.1 wp-dev-env-frame#0.16.0 )

## 0.4.2 - 2022-03-22
Fix Readme and update dependencies

### Changed
- Updated to generator-wp-dev-env#1.3.1 ( wp-dev-env-grunt#1.3.1 wp-dev-env-frame#0.14.1 )

### Fixed
- Fix readme, its filters not actions

## 0.4.1 - 2022-03-08
Updated to generator-wp-dev-env#1.3.0 ( wp-dev-env-grunt#1.3.1 wp-dev-env-frame#0.14.0 )

### Changed
- Updated to generator-wp-dev-env#1.3.0 ( wp-dev-env-grunt#1.3.1 wp-dev-env-frame#0.14.0 )

## 0.4.0 - 2021-11-24
Allow filtering the $excluded_handles for the loader

### Added
- Allow filtering the $excluded_handles. The handles that will not be added to the loader data. Eg to forcefully exclude some more dependencies.

## 0.3.1 - 2021-09-22
Update dependencies

### Changed
- Updated to generator-wp-dev-env#1.1.1 ( wp-dev-env-grunt#1.2.1 wp-dev-env-frame#0.12.0 )

## 0.3.0 - 2021-04-05
Updated readme.txt

### Added
- Create file `docs_hooks.md` on build and watch.

### Changed
- Updated readme.txt

### Fixed
- Handle multible localize data
- Don't mess up with equal handle names of scripts and styles. Allow same handle name for script and style.

## 0.2.0 - 2021-02-09
Change hook priorities to enable woocommerce-blocks to add inline script data wcSettings

### Added
- Filters `acll_loader_hook_priorities`|`acll_cleaner_hook_priorities` to allow hook priorities to be filtered

### Changed
- Hook priorities. To enable woocommerce-blocks to add inline script data wcSettings
- Only load assets when they are not already loaded on start

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
