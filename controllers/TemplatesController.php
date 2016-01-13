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
use app\models\Template;
use app\models\search\TemplateSearch;
use app\models\TemplateCategory;
use app\helpers\ArrayHelper;

/**
 * Class TemplatesController
 * @package app\controllers
 */
class TemplatesController extends Controller
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
                'modelClass' => 'app\models\Template',
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
     * Lists all Template models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Template models.
     *
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategory($id)
    {
        $categoryModel = $this->findCategoryModel($id);
        $query = [
            'TemplateSearch' => [
                'category_id' => $categoryModel->id,
            ],
        ];
        $searchModel = new TemplateSearch();
        $dataProvider = $searchModel->search($query);

        return $this->render('category', [
            'categoryModel' => $categoryModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Template model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Template model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Template();

        $this->disableAssets();

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Template model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $this->disableAssets();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Promote / Non-Promote multiple Templates
     *
     * @param $promoted
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdatePromotion($promoted)
    {

        $templates = Template::findAll(['id' => Yii::$app->getRequest()->post('ids')]);

        if (empty($templates)) {
            throw new NotFoundHttpException(Yii::t('app', 'Page not found.'));
        } else {
            foreach ($templates as $template) {
                $template->promoted = $promoted;
                $template->update();
            }

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('app', 'The selected items have been successfully updated.')
            );

            $referrer = Yii::$app->request->getReferrer();
            $referrer = !isset($referrer) || empty($referrer) ||
            (strpos($referrer, "templates/category") === false) ? ['index'] : $referrer;

            return $this->redirect($referrer);
        }
    }

    /**
     * Updates an existing Template model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionSettings($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            // Select id & name of all template categories in the system
            $categories = TemplateCategory::find()->select(['id', 'name'])->asArray()->all();
            $categories = ArrayHelper::map($categories, 'id', 'name');

            return $this->render('settings', [
                'model' => $model,
                'categories' => $categories,
            ]);
        }
    }

    /**
     * Deletes an existing Template model.
     * If the delete is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Template model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Template the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Template::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the TemplateCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TemplateCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCategoryModel($id)
    {
        if (($model = TemplateCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Disable Assets
     */
    private function disableAssets()
    {
        Yii::$app->assetManager->bundles['app\bundles\AppBundle'] = false;
        Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
        Yii::$app->assetManager->bundles['yii\bootstrap\BootstrapPluginAsset'] = false;
        Yii::$app->assetManager->bundles['yii\web\YiiAsset'] = false;
    }
}
