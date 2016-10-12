const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

// Include JS from bower
jsFromBower = (scripts) => {
    bower_scripts = []
    scripts.forEach((script) => {
        bower_scripts.push(`../bower/${script}.js`)
    })
    return bower_scripts
}

fileFromBower = (file) => {
    return `resources/assets/bower/${file}`
}

filesFromBowerFolder = (folder, files) => {
    bower_files = []
    files.forEach((file) => {
        bower_files.push(fileFromBower(folder + '/' + file))
    })
    return bower_files
}

elixir(mix => {
    mix
        .browserSync({
            port: 8085,
            open: 'external',
            host: 'egerep.app',
            proxy: 'https://egecms.app:8084',
            https: true
        })
        .sass('app.scss')
        .coffee(['resources/assets/coffee/*.coffee', 'resources/assets/coffee/*/*.coffee'])
        .copy(fileFromBower('SVG-Loaders/svg-loaders/three-dots.svg'), 'public/img/svg')
        .scripts(jsFromBower([
            'jquery/dist/jquery',
            'bootstrap/dist/js/bootstrap.min',
            'angular/angular.min',
            'angular-animate/angular-animate.min',
            'angular-sanitize/angular-sanitize.min',
            'angular-resource/angular-resource.min',
            'angular-aria/angular-aria.min',
            'angular-messages/angular-messages.min',
            'angular-material/angular-material.min',
            'angular-i18n/angular-locale_ru-ru',
            'angular-inview/angular-inview',
            'nprogress/nprogress',
            'underscore/underscore-min',
            'bootstrap-select/dist/js/bootstrap-select',
            'bootstrap-datepicker/dist/js/bootstrap-datepicker',
            'bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min',
            'angular-ui-sortable/sortable.min',
            'angular-bootstrap/ui-bootstrap.min',
            'ladda/dist/spin.min',
            'ladda/dist/ladda.min',
            'angular-ladda/dist/angular-ladda.min',
            'moment/min/moment.min',
            'moment/locale/ru',
            'jquery.maskedinput/dist/jquery.maskedinput.min',
            'notifyjs/dist/notify',
        ]).concat(['resources/assets/js/*.js']), 'public/js/vendor.js');
});
