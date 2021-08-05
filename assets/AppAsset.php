<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
//        'css/bootstrap.css',
//        'css/jasny-bootstrap.min.css',
        'css/animate.min.css',
//        'css/jqury-ui.css',
        'css/style.css',
        'css/serviceYo.css',
        'css/style-admin.css',
    ];
    public $js = [
//        'js/jquery.min.js',
//        'js/bootstrap.min.js',
        'js/jasny-bootstrap.min.js',
//        'js/jqury-ui.js',
        'js/jquery.cookie.js', // Библиотека для cookie // https://codernote.ru/jquery/rabota-s-cookies-na-jquery/
        'js/monitor.js',
        'js/main.js',
        'js/datapicker.js',
        'js/datapicker-localize.js',
        'js/ssmp.js',
        'js/serviceYo.js',
        'js/Chart.js',
//        'js/reports.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
//        'yii\bootstrap\PopperAsset.js',
    ];
}
