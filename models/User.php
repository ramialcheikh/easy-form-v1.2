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

namespace app\models;

use Yii;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use app\modules\user\models\UserKey;
use app\helpers\MailHelper;

/**
 * This is the model class for table "tbl_user".
 *
 * @property string    $id
 * @property string    $role_id
 * @property integer   $status
 * @property string    $email
 * @property string    $new_email
 * @property string    $username
 * @property string    $password
 * @property string    $auth_key
 * @property string    $api_key
 * @property string    $login_ip
 * @property string    $login_time
 * @property string    $create_ip
 * @property string    $create_time
 * @property string    $update_time
 * @property string    $ban_time
 * @property string    $ban_reason
 *
 * @property Profile   $profile
 * @property Form[]    $forms
 * @property Theme[]   $themes
 * @property FormUser[] $userForms
 * @property Form[]     $assignedForms
 *
 * @property \app\modules\user\models\Role      $role
 * @property \app\modules\user\models\UserKey[] $userKeys
 * @property \app\modules\user\models\UserAuth[] $userAuths
 */
class User extends \app\modules\user\models\User
{

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForms()
    {
        return $this->hasMany(Form::className(), ['created_by' => 'id'])->inverseOf('author');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThemes()
    {
        return $this->hasMany(Theme::className(), ['created_by' => 'id'])->inverseOf('author');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserForms()
    {
        return $this->hasMany(FormUser::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedForms()
    {
        return $this->hasMany(Form::className(), ['id' => 'user_id'])
            ->via('userForms');
    }

    /**
     * Send email confirmation to user
     *
     * @param UserKey $userKey
     * @return int
     */
    public function sendEmailConfirmation($userKey)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */

        // modify view path to module views
        $mailer           = Yii::$app->mailer;
        $oldViewPath      = $mailer->viewPath;
        $mailer->viewPath = Yii::$app->getModule("user")->emailViewPath;

        // send email
        $user    = $this;
        $profile = $user->profile;
        $email   = $user->new_email !== null ? $user->new_email : $user->email;
        $subject = Yii::$app->settings->get("app.name") . " - " . Yii::t("app", "Email Confirmation");
        $message  = $mailer->compose('confirmEmail', compact("subject", "user", "profile", "userKey"))
            ->setTo($email)
            ->setSubject($subject);

        // Sender by default: Support Email
        $fromEmail = MailHelper::from(Yii::$app->settings->get("app.supportEmail"));

        // Sender verification
        if (empty($fromEmail)) {
            return false;
        }

        $message->setFrom($fromEmail);

        $result = $message->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }
}
