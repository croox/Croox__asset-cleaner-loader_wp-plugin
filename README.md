WordPress Plugin Croox Asset Cleaner Loader

Stop some assets from being enqueued. Use JS to load them when needed

> Plugin readme: [./dist/trunk/README.md](https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin/tree/master/dist/trunk)

# Download and install

~~**Croox Asset Cleaner Loader** is [available in the official WordPress Plugin repository](https://wordpress.org/plugins/Croox__asset-cleaner-loader_wp-plugin/). You can install this plugin the same way you'd install any other plugin.~~

To install it from zip file, [download latest release](https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin/releases/latest).

# Development

Clone the repository and make it your current working directory.

```
# Checkout the `generated` and `develop` branches
git checkout generated && git pull
git checkout develop && git pull

# Install npm dependencies
npm install

# Install composer dependencies
composer install --profile -v

# Build into `./test_build`
grunt build
```

> This Plugin is based on [generator-wp-dev-env](https://github.com/croox/generator-wp-dev-env). See `generator.version` in `package.json`.
>
> Read the [documentation](https://github.com/croox/generator-wp-dev-env#documentation) for further development information.
> -- [Installation & Quick Start](https://htmlpreview.github.io/?https://github.com/croox/generator-wp-dev-env/blob/master/docs/generator-wp-dev-env.docset/Contents/Resources/Documents/Guide/installation_quick_start.html)
> -- [Git branching model](https://htmlpreview.github.io/?https://raw.githubusercontent.com/croox/generator-wp-dev-env/master/docs/generator-wp-dev-env.docset/Contents/Resources/Documents/Guide/git_branching_model.html)
> -- [Project Structure](https://htmlpreview.github.io/?https://raw.githubusercontent.com/croox/generator-wp-dev-env/master/docs/generator-wp-dev-env.docset/Contents/Resources/Documents/Guide/project_structure.html)
> -- [Grunt Taskrunner](https://htmlpreview.github.io/?https://raw.githubusercontent.com/croox/generator-wp-dev-env/master/docs/generator-wp-dev-env.docset/Contents/Resources/Documents/Guide/grunt_taskrunner.html)

#### Dev dependencies

- `node` and `npm`
- `yo` and `generator-wp-dev-env`
- `composer`
- `git`
- `grunt`  and  `grunt-cli`
- `rsync`
- `gettext`
- `convert` from ImageMagick. Tested with ImageMagick `6.8.9-9`

# Support and feedback

* [Create a new issue on Github](https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin/issues/new)
* ~~[Add a new topic to WP's support forum](https://wordpress.org/support/plugin/Croox__asset-cleaner-loader_wp-plugin)~~
* ~~[Create a new review and rate this Plugin](https://wordpress.org/support/plugin/Croox__asset-cleaner-loader_wp-plugin/reviews/#new-post)~~
