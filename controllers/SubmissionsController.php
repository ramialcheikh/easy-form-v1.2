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
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use app\models\FormSubmission;

/**
 * Class SubmissionsController
 * @package app\controllers
 */
class SubmissionsController extends ActiveController
{
    public $modelClass = 'app\models\FormSubmission';
    public $createScenario = 'administration';
    public $updateScenario = 'administration';

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     * Checks the privilege of the current user.
     *
     * @param string $action the ID of the action to be executed
     * @param \yii\base\Model $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // Form ID
        $id = Yii::$app->request->getQueryParam('id');
        // If anonymous user or user without access
        if (!empty(Yii::$app->user) && Yii::$app->user->isGuest || !Yii::$app->user->canAccessToForm($id)) {
            // if access should be denied
            throw new ForbiddenHttpException(
                Yii::t("app", "You are not allowed to perform this action.")
            );
        }

    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function () {
            // Get id param
            $request = \Yii::$app->getRequest();
            $id = $request->get('id');
            $q = $request->get('q');

            $query = FormSubmission::find()->where('form_id=:form_id', [':form_id' => $id]);

            if (isset($q)) {
                $query = FormSubmission::find()
                    ->where('form_id=:form_id', [':form_id' => $id])
                    ->andWhere(['like', 'data', $q]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Yii::$app->params['GridView.pagination.pageSize'],
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ]
            ]);
        };

        return $actions;
    }

    public function actionUpdateall()
    {
        // Get ids param
        $request = \Yii::$app->getRequest();
        $id = $request->post('id');
        $ids = $request->post('ids');
        $attributes = $request->post('attributes');

        // Default
        $success = false;
        $message = Yii::t("app", "No items matched the query");
        $itemsUpdated = 0;

        try {
            // The number of rows updated
            $itemsUpdated = FormSubmission::updateAll($attributes, ['id' => $ids, 'form_id' => $id]);

            if ($itemsUpdated > 0) {
                $success = true;
                $message = Yii::t("app", "Items updated successfully");
            }

        } catch (\Exception $e) {
            // Rethrow the exception
            // throw $e;
            $message = $e->getMessage();
        }

        // Response fornat
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Response to Client
        $res = array(
            'success' => $success,
            'action'  => 'updateall',
            'itemsUpdated' => $itemsUpdated,
            'ids' => $ids,
            'attributes' => $attributes,
            'message' => $message,
        );

        return $res;

    }

    public function actionDeleteall()
    {
        // Get ids param
        $request = Yii::$app->getRequest();
        $id = $request->post('id');
        $ids = $request->post('ids');

        // Default
        $success = false;
        $message = "No items matched the query";
        $itemsDeleted = 0;

        try {
            // The number of rows deleted
            $itemsDeleted = 0;
            // Delete one to one for trigger events
            foreach (FormSubmission::find()->where(['id' => $ids, 'form_id' => $id])->all() as $submissionModel) {
                $deleted = $submissionModel->delete();
                if ($deleted) {
                    $itemsDeleted++;
                }
            }
            // Set response
            if ($itemsDeleted > 0) {
                $success = true;
                $message = Yii::t("app", "Items deleted successfully");
            }

        } catch (\Exception $e) {
            // Rethrow the exception
            // throw $e;
            $message = $e->getMessage();
        }

        // Response fornat
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Response to Client
        $res = array(
            'success' => $success,
            'action'  => 'deleteall',
            'itemsDeleted' => $itemsDeleted,
            'ids' => $ids,
            'message' => $message,
        );

        return $res;

    }
}
