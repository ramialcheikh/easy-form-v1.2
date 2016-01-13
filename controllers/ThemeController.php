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
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\Form;
use app\models\Theme;
use app\models\search\ThemeSearch;

/**
 * Class ThemeController
 * @package app\controllers
 */
class ThemeController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'delete-multiple' => ['post'],
                ],
            ],
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

    public function actions()
    {
        return [
            'delete-multiple' => [
                'class' => '\mickgeek\actionbar\DeleteMultipleAction',
                'modelClass' => 'app\models\Theme',
                'afterDeleteCallback' => function () {
                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app', 'The selected items have been successfully deleted.')
                    );
                },
            ],
        ];
    }

    /**
     * Lists all Theme models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ThemeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Theme model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'themeModel' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Theme model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $themeModel = new Theme();
        $forms = Form::find()->select(['id', 'name'])->asArray()->all();

        if ($themeModel->load(Yii::$app->request->post()) && $themeModel->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'The theme has been successfully created.'));
            return $this->redirect(['view', 'id' => $themeModel->id]);
        } else {
            return $this->render('create', [
                'themeModel' => $themeModel,
                'forms' => $forms,
            ]);
        }
    }

    /**
     * Updates an existing Theme model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $themeModel = $this->findModel($id);
        $forms = Form::find()->select(['id', 'name'])->asArray()->all();

        if ($themeModel->load(Yii::$app->request->post()) && $themeModel->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'The theme has been successfully updated.'));
            return $this->redirect(['view', 'id' => $themeModel->id]);
        } else {
            return $this->render('update', [
                'themeModel' => $themeModel,
                'forms' => $forms,
            ]);
        }
    }

    /**
     * Deletes an existing Theme model.
     * If the delete is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'The theme has been successfully deleted.'));

        return $this->redirect(['index']);
    }

    /**
     * Finds the Theme model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Theme the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($themeModel = Theme::findOne($id)) !== null) {
            return $themeModel;
        } else {
            throw new NotFoundHttpException(Yii::t("app", "The requested page does not exist."));
        }
    }
}
