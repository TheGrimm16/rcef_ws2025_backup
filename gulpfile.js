var elixir = require('laravel-elixir');


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

elixir(function(mix) {
    mix.sass('app.scss');

    mix.styles([
        '../../../node_modules/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css',
        '../../../node_modules/gentelella/vendors/font-awesome/css/font-awesome.min.css',
        '../../../node_modules/gentelella/vendors/nprogress/nprogress.css',
        '../../../node_modules/gentelella/vendors/iCheck/skins/flat/green.css',
        '../../../node_modules/gentelella/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css',
        '../../../node_modules/gentelella/vendors/jqvmap/dist/jqvmap.min.css',
        '../../../node_modules/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css',
        '../../../node_modules/gentelella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css',
        '../../../node_modules/gentelella/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css',
        '../../../node_modules/gentelella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css',
        '../../../node_modules/gentelella/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css',
        '../../../node_modules/gentelella/build/css/custom.min.css',
        '../../../public/assets/Holdon/HoldOn.min.css',
        '../../../public/assets/jquery-ui-1.12.1/jquery-ui.min.css'
    ], 'public/css/all.css');

    mix.scripts([
        '../../../node_modules/gentelella/vendors/jquery/dist/jquery.min.js',
        '../../../node_modules/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js',
        '../../../node_modules/gentelella/vendors/fastclick/lib/fastclick.js',
        '../../../node_modules/gentelella/vendors/nprogress/nprogress.js',
        '../../../node_modules/gentelella/vendors/Chart.js/dist/Chart.min.js',
        '../../../node_modules/gentelella/vendors/gauge.js/dist/gauge.min.js',
        '../../../node_modules/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js',
        '../../../node_modules/gentelella/vendors/iCheck/icheck.min.js',
        '../../../node_modules/gentelella/vendors/skycons/skycons.js',
        '../../../node_modules/gentelella/vendors/moment/min/moment.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net/js/jquery.dataTables.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons/js/buttons.flash.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons/js/buttons.html5.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-buttons/js/buttons.print.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js',
        '../../../node_modules/gentelella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js',
        '../../../node_modules/gentelella/vendors/datatables.net-scroller/js/dataTables.scroller.min.js',
        '../../../node_modules/gentelella/build/js/custom.min.js',
        '../../../public/assets/instascan/instascan.min.js',
        '../../../public/assets/Holdon/HoldOn.min.js',
        '../../../public/assets/jquery-ui-1.12.1/jquery-ui.min.js',
        'script.js'
    ], 'public/js/app.js');
});
