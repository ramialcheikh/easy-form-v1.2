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

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\base\Model;
use app\models\Setting;

/**
 * Class SettingsController
 * @package app\controllers
 */
class SettingsController extends Controller
{

    public $defaultAction = 'site';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            // Check for admin permission
                            // Note: Check for Yii::$app->user first because it doesn't exist in console commands
                            if (!empty(Yii::$app->user) && Yii::$app->user->can("admin")) {
                                return true;
                            }

                            // By Default, Denied Access
                            return false;
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Update App Settings
     *
     * @return string
     */
    public function actionSite()
    {

        $this->layout = 'admin'; // In @app/views/layouts

        $settings = Setting::find()->where(['category' => 'app'])->orderBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            /** @var \app\models\Setting $setting */
            foreach ($settings as $setting) {
                $setting->save(false);
            }

            // Show success alert
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('app', 'The site settings have been successfully updated.')
            );
        }

        return $this->render('site', ['settings' => $settings]);
    }

    public function actionMail()
    {

        $this->layout = 'admin'; // In @app/views/layouts
        $settings = Setting::find()->where(['category' => 'smtp'])->orderBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            /** @var \app\models\Setting $setting */
            foreach ($settings as $setting) {
                $setting->save(false);
            }

            // Show success alert
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('app', 'The smtp server settings have been successfully updated.')
            );
        }

        return $this->render('mail', ['settings' => $settings]);
    }
}
