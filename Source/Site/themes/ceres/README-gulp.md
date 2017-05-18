# ArkDev Front-End Seed Project
A simple frontend project based on _Gulp_ for task management, SCSS with _Bootstrap_ and _Fontawesome_ for base CSS framework and font.

# Install
1. Download the the project archive and include it in your project.
    >__INPUT__: SCSS files at `src/scss`

    >__OUTPUT__: CSS files at `dist/css`
2. Globally install `Node.js`, `Bower` and `Gulp` on your operating system. Skip this step if you already have them installed before:
    * **Install Node.js**: `Bower` depends on Node.js and `NPM` so you need to get Node.js. Just download the installation package from Node.js site and click through it.
    * **Install Bower**: After you have Node.js installed we can install Bower with npm. You might need to restart your Windows to get all the path variables setup so Npm can find them.

        Open up the Git Bash or Command Prompt **(As Administrator)** and install Bower globally by running the following command.
        ```bash
        npm install -g bower
        ```
    * **Install Gulp globally**: 
        
        ```bash
        npm install --global gulp
        ```
3. Install packages and dependencies configured in `bower.json` using this command:
    ```bash
    bower install
    ```
    You should see `bower_components` folder created with all packages.
4. Install Gulp modules dependencies configured in `package.json`:

        ```
        npm install --save-dev
        ```
    
    You should see `node_modules` folder created with all gulp modules.
    
    > Make sure `bower_components` and `node_modules` folders are added to your project ignore list in `.gitignore` file.

# Configure gulpfile.js
* If your styles will be in both LTR/RTL layouts, edit `css` task to enable RTL styles by including `style-rtl.scss` and use `bootstrap-sass-rtl` instead of `bootstrap-sass`. Your `css` task should look like this:
    ```
    return gulp.src([
    config.sassPath + '/style-rtl.scss',
    config.sassPath + '/style.scss'
    ]).pipe(sass({
    style: 'expanded',
    includePaths: [
        config.sassPath,
        config.bowerDir + '/bootstrap-sass-rtl/src/stylesheets/bootstrap',
        config.bowerDir + '/fontawesome/scss'
    ]
    ...
    ```
    > If your styles will be in LTR layout only, leave `css` task as is.
        
* Edit `scripts` task to include/exclude any of existing JavaScript libraries:
    * jQuery if it is not already included within your page context.
    * Bootstrap components `affix.js`, `alert.js`, `button.js`, `carousel.js`, `collapse.js`, `dropdown.js`, `modal.js`, `popover.js`, `scrollspy.js`, `tab.js`, `tooltip.js` and/or `transition.js`.
        > Adding any of these components will require including the coressponding `scss` file in `/src/scss/core/_bootstrap-custom.scss`
        
    * `carousel-swipe.js` to add swipe behavior to Bootstrap's Carousel if `carousel.js` included above.
    * `bootstrap-dialog.js` to make use of Bootstrap Modal `modal.js` more friendly. [Examples] (http://nakupanda.github.io/bootstrap3-dialog/)
        > This plugin require including `modal.js` within `scripts` task and including `modals` in `/src/scss/core/_bootstrap-custom.scss`
            

# Development
During any `SSCSS` or `JavaScript` development, you need to run `gulp` default task by this command:
```bash
gulp
```

#### Gulp `default` task contains the following sub-tasks:
* **icons**: copy `Font-Awesome` font files from `bower_components` to `dist/fonts`.
* **css**: compile `style.scss` and `style-rtl.scss` files from `src/scss` to `dist/css` and generate a minified `.min` css version.
* **scripts**: compile all your files from `src/js` to `dist/js` and generate a minified `.min` js version.
* **watch**: watch the _SCSS_ files for change to call `css` tasks and watch the _JS_ files for change to call `scripts` tasks.

---
## Resources
* [ArkDev Front-End Under Construction Starter Page] (http://dev-server/front-end/under-construction)