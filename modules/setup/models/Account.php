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

namespace app\modules\setup\models;

use yii\db\ActiveRecord;

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
 */
class Account extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }
}
