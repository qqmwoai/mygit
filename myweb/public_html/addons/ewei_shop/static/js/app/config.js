require.config({
    baseUrl: '../addons/ewei_shop/static/js/app',
    paths: {
        'jquery': '../dist/jquery-1.11.1.min',
        'jquery.ui': '../dist/jquery-ui-1.10.3.min',
        'bootstrap': '../dist/bootstrap.min',
        'tpl':'../dist/tmodjs',
        'jquery.touchslider':'../dist/jquery.touchslider.min',
        'swipe':'../dist/swipe'
        
    },
    shim: {
        'jquery.ui': {
            exports: "$",
            deps: ['jquery']
        },
        'bootstrap': {
            exports: "$",
            deps: ['jquery']
        },  
        'jquery.touchslider': {
            exports: "$",
            deps: ['jquery']
        }
    }
});