<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wpjCode\wii;

use yii\web\AssetBundle;

/**
 * This declares the asset files required by Gii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GiiAsset extends AssetBundle
{
    public $sourcePath = '@wpjCode/wii/assets';
    public $css = [
        'css/main.css',
        'plugin/ztree/css/zTreeStyle.css',
        'css/hightlight-github.css'
    ];
    public $js = [
        'js/bootstrap.js',
        'js/bs4-native.min.js',
        'js/gii.js',
        'js/alert.js?v=6',
        'plugin/ztree/js/jquery.ztree.core.min.js',
        'js/custom.js',
        'js/highlight.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}
