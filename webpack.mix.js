const mix = require('laravel-mix');
const productionSourceMaps = !mix.inProduction();

// JS
mix.js('resources/js/vendors.js', 'public/js/vendors.js');
mix.js(
    [
        'resources/js/custom.js',
        'resources/js/custom/tables/tables.js',
        'resources/js/custom/charts/chart-area-demo.js',
        'resources/js/custom/charts/chart-bar-demo.js',
        'resources/js/custom/charts/chart-pie-demo.js',
    ],
    'public/js/custom.js'
).sourceMaps(productionSourceMaps, 'source-map').version();

// CSS
mix.copyDirectory('resources/fonts', 'public/fonts');
mix.copyDirectory('resources/img', 'public/img');
mix.styles(
    [
        'resources/css/vendors/dataTables.bootstrap4.min.css',
        'resources/css/vendors/fonts.googleapis.nunito.css',
        'resources/css/vendors/font-awesome.min.css',
        'resources/css/vendors/owl.carousel.min.css',
        'resources/css/vendors/owl.theme.default.min.css',
        'resources/css/vendors/select2.css',
        'resources/css/vendors/select2-bootstrap.css',
        'resources/css/vendors/sb-admin.css',
    ],
    'public/css/vendors.css'
)
mix.styles('resources/css/custom.css', 'public/css/custom.css').version();
