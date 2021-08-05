<?php


namespace app\assets;

use yii\web\AssetBundle;

class DoctorAsst extends AssetBundle
{
    public $sourcePath = '@app/modules/doctor/web';
    public $css = [

        'css/animate/4.1.1/animate.min.css',
        'css/style.css',
        'css/jquery-ui.css',
        'css/font-awesome/css/font-awesome.css',
        'css/loader_animate.css',
    ];
    public $js = [

        'js/jquery-ui.js',
        'js/main.js',
        'js/doctor.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}