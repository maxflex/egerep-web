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
        .scripts([
            '../bower/jquery/dist/jquery.js',
            '../bower/bootstrap/dist/js/bootstrap.min.js',
            '../bower/angular/angular.min.js',
            '../bower/angular-animate/angular-animate.min.js',
            '../bower/angular-sanitize/angular-sanitize.min.js',
            '../bower/angular-resource/angular-resource.min.js',
            '../bower/angular-aria/angular-aria.min.js',
            '../bower/angular-messages/angular-messages.min.js',
            '../bower/angular-material/angular-material.min.js',
            '../bower/angular-i18n/angular-locale_ru-ru.js',
            '../bower/angular-inview/angular-inview.js',
            '../bower/nprogress/nprogress.js',
            '../bower/underscore/underscore-min.js',
            '../bower/bootstrap-select/dist/js/bootstrap-select.js',
            '../bower/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
            '../bower/bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min.js',
            '../bower/angular-ui-sortable/sortable.min.js',
            '../bower/angular-bootstrap/ui-bootstrap.min.js',
            '../bower/ladda/dist/ladda.min.js',
            '../bower/angular-ladda/dist/angular-ladda.min.js',
            '../bower/moment/min/moment.min.js',
            '../bower/moment/locale/ru.js',
            '../bower/jquery.maskedinput/dist/jquery.maskedinput.min.js',
            'resources/assets/js/*.js',
        ], 'public/js/vendor.js');
});
