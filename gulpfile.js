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
        .sass('mobile.scss')
        .coffee(['resources/assets/coffee/*.coffee', 'resources/assets/coffee/*/*.coffee'])
        .copy(fileFromBower('SVG-Loaders/svg-loaders/three-dots.svg'), 'public/img/svg')
        .copy(fileFromBower('egerep-svg-metro/views/map.svg'), 'public/img/svg/map.svg')
        .scripts(jsFromBower([
            'jquery/dist/jquery',
            'angular/angular.min',
            'angular-resource/angular-resource.min',
            'angular-i18n/angular-locale_ru-ru',
            'underscore/underscore-min',
            'ladda/dist/spin.min',
            'ladda/dist/ladda.min',
            'angular-ladda/dist/angular-ladda.min',
            'moment/min/moment.min',
            'moment/locale/ru',
            'jquery.maskedinput/dist/jquery.maskedinput.min',
            'notifyjs/dist/notify',
            'jquery-ui/jquery-ui.min',
            'jquery.customSelect/jquery.customSelect.min',
            'iCheck/icheck.min',
            'angular-file-upload/dist/angular-file-upload.min',
            'bootstrap-select/dist/js/bootstrap-select.min',
            'bootstrap/js/bootstrap.min',
            'jquery.inputmask/dist/jquery.inputmask.bundle',
            'mark.js/dist/jquery.mark.min',
            'angular-toArrayFilter/toArrayFilter',
            'jquery.cookie/jquery.cookie',
            'egerep-svg-metro/scripts/svg',
            'jquery.panzoom/dist/jquery.panzoom',
            'protonet/jquery.inview/jquery.inview.min',
        ]).concat(['resources/assets/js/*.js']), 'public/js/vendor.js');
});
