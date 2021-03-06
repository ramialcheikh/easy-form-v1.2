<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.1
 * @author Balu
 * @copyright Copyright (c) 2015 Baluart.COM
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link http://easyforms.baluart.com/ Easy Forms
 */

namespace app\helpers;

use Yii;

/**
 * Class Mailer
 * @package app\helpers
 *
 * Add business logic related to sending emails
 */
class MailHelper
{

    /**
     * Return the sender email address according to app configuration
     *
     * @param string $sender Email by default
     * @return string Email address
     */
    public static function from($sender = '')
    {

        /** @var \app\components\queue\MailQueue $mailer */
        $mailer = Yii::$app->mailer;

        // Check for messageConfig before sending (for backwards-compatible purposes)
        if (isset($mailer->messageConfig, $mailer->messageConfig["from"]) &&
            !empty($mailer->messageConfig["from"])) {
            $sender = $mailer->messageConfig["from"];
        } elseif (isset(Yii::$app->params['App.Mailer.transport']) &&
            Yii::$app->params['App.Mailer.transport'] === 'smtp') {
            // Set smtp username as sender
            $sender = Yii::$app->settings->get("smtp.username");
        }

        return $sender;
    }
}
