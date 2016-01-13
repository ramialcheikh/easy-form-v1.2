<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.0
 * @author Balu
 * @copyright Copyright (c) 2015 Baluart.COM
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link http://easyforms.baluart.com/ Easy Forms
 */

namespace app\bundles;

use Yii;
use yii\web\AssetBundle;

/**
 * Class SubmissionsBundle
 *
 * @package app\bundles
 */
class SubmissionsBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/static_files';
    public $css = [
    ];
    public $js = [
        'js/libs/underscore.js',
        'js/libs/backbone.js',
        'js/libs/jquery.cookie.js',
        'js/submissions.min.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset', // Load jquery.js and bootstrap.js first
    ];
    public function init()
    {
        // if( !empty(Yii::$app->params['Google.Maps.apiKey']) ) {
            array_unshift($this->js, 'http://maps.google.com/maps/api/js?key=' .
                Yii::$app->params['Google.Maps.apiKey']);
        // }
        parent::init();
    }
}
