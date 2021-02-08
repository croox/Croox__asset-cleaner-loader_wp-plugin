# Croox Asset Cleaner Loader #
**Tags:** load,script,async,clean,assets  
**Donate link:** https://github.com/croox/donate  
**Contributors:** [croox](https://profiles.wordpress.org/croox)  
**Tested up to:** 5.6.1  
**Requires at least:** 5.0.0  
**Requires PHP:** 5.6.0  
**Stable tag:** trunk  
**License:** GNU General Public License v2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Stop some assets from being enqueued. Use JS to load them when needed


## Description ##


## Installation ##
Upload and install this Theme the same way you'd install any other Theme.


## Screenshots ##


## Upgrade Notice ##



# 

## Changelog ##

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
