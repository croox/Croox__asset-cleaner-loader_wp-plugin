# Action and Filter Hooks


## File: /classes/Cleaner.php

- `filter` `acll_cleaner_hook_priorities`
  
  Allow hook priorities to be filtered.
  
  #### Params:
  
  - `array` $priorities
  
    Associative array of hook priorities.

- `filter` `acll_cleaner_scripts`
  
  Filters the array of script handles that will be dequeued.
  
  #### Params:
  
  - `array` $dequeue_script_handles
  
    Script handles to be dequeued.

- `filter` `acll_cleaner_styles`
  
  Filters the array of style handles that will be dequeued.
  
  #### Params:
  
  - `array` $dequeue_style_handles
  
    Style handles to be dequeued.

- `filter` `acll_cleaner_woo_block_scripts_dependencies`
  
  Modify script handles to be removed from all woo block script dependencies
  
  #### Params:
  
  - `array` $handles
  
    Script handles to be removed from all woo block script dependencies


## File: /classes/Loader.php

- `filter` `acll_loader_hook_priorities`
  
  Allow hook priorities to be filtered.
  
  #### Params:
  
  - `array` $priorities
  
    Associative array of hook priorities.

- `filter` `acll_loader_style_handles`
  
  Modifies the array of style handles that will available for frontend js.
  
  #### Params:
  
  - `array` $style_handles
  
    Style handles for frontend js.

- `filter` `acll_loader_script_handles_header`
  
  Modifies the array of script handles, collected in header, that will available for frontend js..
  
  #### Params:
  
  - `array` $script_handles
  
    Header script handles for frontend js.

- `filter` `acll_loader_script_handles_footer`
  
  Modifies the array of script handles, collected in footer, that will available for frontend js..
  
  #### Params:
  
  - `array` $script_handles
  
    Footer script handles for frontend js.

- `filter` `acll_loader_style_exclude_handles`
  
  Allow filtering the $excluded_handles to forcefully exclude some more dependencies.
  
  #### Params:
  
  - `array` $exclude_handles
  
    Style handles to be excluded from loader data.

- `filter` `acll_loader_script_exclude_handles`
  
  Allow filtering the $excluded_handles to forcefully exclude some more dependencies.
  
  #### Params:
  
  - `array` $exclude_handles
  
    Script handles to be excluded from loader data.
