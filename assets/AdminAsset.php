<?php
namespace app\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        // Sesuaikan dengan letak file CSS tema Bootstrap baru Anda
        'assets/css/bootstrap.min.css',
        'assets/css/icons.min.css',
        'assets/css/app.min.css',
    ];
    
    public $js = [
        // JS bawaan tema (Bootstrap bundle, plugin pendukung)
        'assets/libs/bootstrap/js/bootstrap.bundle.min.js',
        'assets/libs/simplebar/simplebar.min.js',
        'https://apexcharts.com/samples/assets/stock-prices.js',
        'assets/js/pages/index.init.js',
        'assets/js/app.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset', // Menjaga library inti Yii & CSRF tetap aktif
        'yii\bootstrap5\BootstrapAsset'
    ];
}