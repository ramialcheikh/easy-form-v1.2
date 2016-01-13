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

namespace app\modules\setup\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\Cookie;
use app\modules\setup\models\forms\DBForm;
use app\modules\setup\models\forms\UserForm;
use app\modules\setup\helpers\SetupHelper;

class StepController extends Controller
{
    public $layout = 'setup';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->language = isset(Yii::$app->request->cookies['language']) ? (string)Yii::$app->request->cookies['language'] : 'en-US';

        if (!parent::beforeAction($action)) {
            return false;
        }

        return true; // or false to not run the action
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function action1()
    {
        if (Yii::$app->request->post('language')) {

            $language = Yii::$app->request->post('language');
            Yii::$app->language = $language;

            $languageCookie = new Cookie([
                'name' => 'language',
                'value' => $language,
                'expire' => time() + 60 * 60 * 24, // 1 day
            ]);

            Yii::$app->response->cookies->add($languageCookie);

            $this->redirect(['2']);
        }

        return $this->render('1');
    }

    public function action2()
    {
        return $this->render('2');
    }

    public function action3()
    {
        $dbForm = new DBForm();
        $connectionOk = false;

        if ($dbForm->load(Yii::$app->request->post()) && $dbForm->validate()) {
            if ($dbForm->testConnection()) {
                if (isset($_POST['test'])) {
                    $connectionOk = true;
                    Yii::$app->session->setFlash('success', Yii::t('setup', 'Database connection - ok'));
                }
                if (isset($_POST['save'])) {
                    $config = SetupHelper::createDatabaseConfig($dbForm->getAttributes());
                    if (SetupHelper::createDatabaseConfigFile($config) === true) {
                        return $this->render('4');
                    }
                    Yii::$app->session->setFlash('warning', Yii::t('setup', 'Unable to create db config file'));
                }
            }
        }

        return $this->render('3', ['model' => $dbForm, 'connectionOk' => $connectionOk]);
    }

    public function action4()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $result = SetupHelper::runMigrations();
            return $result;
        }

        return '';
    }

    public function action5()
    {
        $userForm = new UserForm();

        if ($userForm->load(Yii::$app->request->post()) && $userForm->save()) {
            $this->redirect(['6']);
        }

        return $this->render('5', [
            'model' => $userForm,
        ]);
    }

    public function action6()
    {
        return $this->render('6');
    }
}
