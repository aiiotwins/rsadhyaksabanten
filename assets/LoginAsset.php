<?php
namespace app\assets;

use yii\web\AssetBundle;

class LoginAsset extends AssetBundle
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
        
    ];
    
    public $depends = [
        'yii\web\YiiAsset', // Menjaga library inti Yii & CSRF tetap aktif
        'yii\bootstrap5\BootstrapAsset'
    ];
}