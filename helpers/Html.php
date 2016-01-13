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

namespace app\helpers;

/**
 * Class Html
 * @package app\helpers
 * @extends \yii\helpers\Html
 */
class Html extends \yii\helpers\Html
{
    /**
     * Remove scripts tags from html code
     * @param $html
     * @return mixed
     */
    public static function removeScriptTags($html)
    {

        return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    }
}
