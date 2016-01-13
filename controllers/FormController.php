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
use yii\base\Model;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use SplTempFileObject;
use League\Csv\Writer;
use app\models\Form;
use app\models\search\FormSearch;
use app\models\FormSubmission;
use app\models\Theme;
use app\models\Template;
use app\helpers\ArrayHelper;

/**
 * Class FormController
 * @package app\controllers
 */
class FormController extends Controller
{

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        // This actions can be performed by any user
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function () {
                            // Check for user permission
                            if (!empty(Yii::$app->user) && !Yii::$app->user->isGuest) {
                                return true;
                            }
                            return false;
                        }
                    ],
                    [
                        // This actions can be performed by users with access to form
                        'actions' => ['view', 'share', 'analytics', 'stats', 'submissions',
                            'export-submissions', 'report'],
                        'allow' => true,
                        'matchCallback' => function () {
                            // Check for user permission
                            if (!empty(Yii::$app->user) && !Yii::$app->user->isGuest) {

                                // Form ID
                                $id = Yii::$app->request->getQueryParam('id');
                                return Yii::$app->user->canAccessToForm($id);

                            }
                            return false;
                        }
                    ],
                    [
                        // The rest of actions, only can be performed by a admin users
                        'allow' => true,
                        'matchCallback' => function () {
                            // Check for admin permission
                            if (!empty(Yii::$app->user) && Yii::$app->user->can("admin")) {
                                return true;
                            }
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
                'modelClass' => 'app\models\Form',
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
     * Lists all Form models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FormSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Only for admin users
        if (!empty(Yii::$app->user) &&  Yii::$app->user->can("admin") && ($dataProvider->totalCount == 0)) {
            Yii::$app->getSession()->setFlash(
                'warning',
                Html::tag('strong', Yii::t("app", "You don't have any forms!")) . ' ' .
                Yii::t("app", "Click the blue button on the left to start building your first form.")
            );
        }

        // Select slug & name of all promoted templates in the system. Limit to 5 results.
        $templates = Template::find()->select(['slug', 'name'])->where([
            'promoted' => Template::PROMOTED_ON,
        ])->limit(5)->orderBy('updated_at DESC')->asArray()->all();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'templates' => $templates,
        ]);
    }

    /**
     * Show form builder to create a Form model.
     *
     * @param string $template
     * @return string
     */
    public function actionCreate($template = 'default')
    {

        $this->disableAssets();

        return $this->render('create', [
            'template' => $template
        ]);
    }

    /**
     * Show form builder to update Form model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id = null)
    {
        $this->disableAssets();

        $model = $this->findFormModel($id);

        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
     * Enable / Disable multiple Forms
     *
     * @param $status
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdateStatus($status)
    {

        $forms = Form::findAll(['id' => Yii::$app->getRequest()->post('ids')]);

        if (empty($forms)) {
            throw new NotFoundHttpException(Yii::t('app', 'Page not found.'));
        } else {
            foreach ($forms as $form) {
                $form->status = $status;
                $form->update();
            }
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('app', 'The selected items have been successfully updated.')
            );
            return $this->redirect(['index']);
        }
    }

    /**
     * Updates an existing Form model (except id).
     * Updates an existing FormData model (only data field).
     * Updates an existing FormConfirmation model (except id & form_id).
     * Updates an existing FormEmail model (except id & form_id).
     * If update is successful, the browser will be redirected to the 'index' page.
     *
     * @param int|null $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionSettings($id = null)
    {
        /** @var \app\models\Form $formModel */
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;
        $formConfirmationModel = $formModel->formConfirmation;
        $formEmailModel = $formModel->formEmail;
        $formUIModel = $formModel->ui;

        $postData = Yii::$app->request->post();

        if ($formModel->load($postData) && $formConfirmationModel->load($postData)
            && $formEmailModel->load($postData) && $formUIModel->load($postData)
            && Model::validateMultiple([$formModel, $formConfirmationModel, $formEmailModel, $formUIModel])) {

            // Save data in single transaction
            $transaction = Form::getDb()->beginTransaction();
            try {
                // Save Form Model
                if (!$formModel->save()) {
                    throw new \Exception(Yii::t("app", "Error saving Form Model"));
                }
                // Save data field in FormData model
                if (isset($postData['Form']['name'])) {
                    // Convert JSON Data of Form Data Model to PHP Array
                    /** @var \app\components\JsonToArrayBehavior $builderField */
                    $builderField = $formDataModel->behaviors['builderField'];
                    // Set form name by json key path. If fail, throw \ArrayAccessException
                    $builderField->setSafeValue(
                        'settings.name',
                        $postData['Form']['name']
                    );
                    // Save to DB
                    $builderField->save(); // If fail, throw \Exception
                }
                // Save FormConfirmation Model
                if (!$formConfirmationModel->save()) {
                    throw new \Exception(Yii::t("app", "Error saving Form Confirmation Model"));
                }
                // Save FormEmail Model
                if (!$formEmailModel->save()) {
                    throw new \Exception(Yii::t("app", "Error saving Form Email Model"));
                }
                // Save FormUI Model
                if (!$formUIModel->save()) {
                    throw new \Exception(Yii::t("app", "Error saving Form UI Model"));
                }

                $transaction->commit();

                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t('app', 'The form settings have been successfully updated')
                );

                return $this->redirect(['index']);
            } catch (\Exception $e) {
                // Rolls back the transaction
                $transaction->rollBack();
                // Rethrow the exception
                throw $e;
            }

        } else {

            // Select id & name of all themes in the system
            $themes = Theme::find()->select(['id', 'name'])->asArray()->all();
            $themes = ArrayHelper::map($themes, 'id', 'name');

            return $this->render('settings', [
                'formModel' => $formModel,
                'formDataModel' => $formDataModel,
                'formConfirmationModel' => $formConfirmationModel,
                'formEmailModel' => $formEmailModel,
                'formUIModel' => $formUIModel,
                'themes' => $themes,
            ]);
        }

    }

    /**
     * Displays a single Form Data Model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $formModel = $this->findFormModel($id);

        return $this->render('view', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * Displays a single Form Rule Model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionRules($id)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        return $this->render('rule', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel,
        ]);
    }

    /**
     * Displays share options.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionShare($id)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        return $this->render('share', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel,
        ]);
    }

    /**
     * Display form performance analytics page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionAnalytics($id)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        return $this->render('analytics', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel,
        ]);
    }

    /**
     * Displays form submissions stats page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionStats($id)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        return $this->render('stats', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel,
        ]);
    }

    /**
     * Deletes an existing Form model (and relations).
     * If the delete is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        // Delete Form model
        $this->findFormModel($id)->delete();

        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'The form has been successfully deleted'));

        return $this->redirect(['index']);
    }

    /**
     * Show form submissions.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionSubmissions($id = null)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        return $this->render('submissions', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel
        ]);
    }

    /**
     * Export form submissions.
     *
     * @param integer $id
     */
    public function actionExportSubmissions($id)
    {

        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;

        $query = FormSubmission::find()
            ->select(['data', 'created_at'])
            ->where('form_id=:form_id', [':form_id' => $id])->asArray();

        // Create the CSV into memory
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Insert fields names as the CSV header
        $header = array_values($formDataModel->getLabels());
        array_push($header, 'Submitted at');
        $csv->insertOne($header);

        // To iterate the row one by one
        foreach ($query->each() as $submission) {
            // $submission represents one row of data from the form_submission table
            $data = Json::decode($submission['data'], true);
            foreach ($data as &$field) {
                if (is_array($field)) {
                    $field = implode(', ', $field);
                }
            }
            $data["created_at"] = Yii::$app->formatter->asDatetime($submission['created_at']);
            $csv->insertOne($data);
        }

        // Print to the output stream
        $csv->output($formModel->name . '.csv');
    }

    /**
     * Show form submissions report.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionReport($id = null)
    {
        $formModel = $this->findFormModel($id);
        $formDataModel = $formModel->formData;
        $charts = $formModel->getFormCharts()->asArray()->all();

        return $this->render('report', [
            'formModel' => $formModel,
            'formDataModel' => $formDataModel,
            'charts' => $charts
        ]);
    }

    /**
     * Finds the Form model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * If the user does not have access, a Forbidden Http Exception will be thrown.
     *
     * @param $id
     * @return Form
     * @throws NotFoundHttpException
     */
    protected function findFormModel($id)
    {
        if (($model = Form::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t("app", "The requested page does not exist."));
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
