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

namespace app\components;

use Yii;
use app\helpers\ArrayHelper;

/**
 * Class User
 * @package app\components
 *
 * User Component
 */
class User extends \app\modules\user\components\User
{
    /**
     * @inheritdoc
     */
    public $identityClass = 'app\models\User';

    /**
     * @inheritdoc
     */
    public $enableAutoLogin = true;

    /**
     * @inheritdoc
     */
    public $loginUrl = ["/user/login"];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // check if user is banned. if so, log user out and redirect home
        /** @var \app\models\User $user */
        $user = $this->getIdentity();
        if ($user && $user->ban_time) {
            $this->logout();
            Yii::$app->getResponse()->redirect(['/'])->send();
            return;
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function getIsLoggedIn()
    {
        return !$this->getIsGuest();
    }

    /**
     * @inheritdoc
     */
    public function afterLogin($identity, $cookieBased, $duration)
    {
        /** @var \app\models\User $identity */
        $identity->updateLoginMeta();
        parent::afterLogin($identity, $cookieBased, $duration);
    }

    /**
     * Get user's display name
     *
     * @param string $default
     * @return string
     */
    public function getDisplayName($default = "")
    {
        /** @var \app\models\User $user */
        $user = $this->getIdentity();
        return $user ? $user->getDisplayName($default) : "";
    }

    /**
     * Check if user can do $permissionName.
     * If "authManager" component is set, this will simply use the default functionality.
     * Otherwise, it will use our custom permission system
     *
     * @param string $permissionName
     * @param array  $params
     * @param bool   $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager to call parent
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            return parent::can($permissionName, $params, $allowCaching);
        }

        // otherwise use our own custom permission (via the role table)
        /** @var \app\models\User $user */
        $user = $this->getIdentity();
        return $user ? $user->can($permissionName) : false;
    }

    /**
     * Form ids assigned to this user
     *
     * @return array
     */
    public function getAssignedFormIds()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $userForms = $user->getUserForms()->asArray()->all();
        $userForms = ArrayHelper::getColumn($userForms, 'form_id');
        return $userForms;
    }

    /**
     * Check if user can access to Form.
     *
     * @param integer $id Form ID
     * @return bool
     */
    public function canAccessToForm($id)
    {
        if (isset(Yii::$app->user) && Yii::$app->user->can('admin')) {
            return true;
        } else { // If not admin
            $formIds = $this->getAssignedFormIds();
            if (count($formIds) > 0 && in_array($id, $formIds)) {
                return true;
            }
        }

        return false;
    }
}
