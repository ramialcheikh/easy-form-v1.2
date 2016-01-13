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
use app\models\FormRule;

/**
 * Class RulesController
 * @package app\controllers
 */
class RulesController extends ActiveController
{
    public $modelClass = 'app\models\FormRule';

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
        // Check for admin permission
        if (!empty(Yii::$app->user) && !Yii::$app->user->can("admin")) {
            throw new ForbiddenHttpException(Yii::t("app", "You are not allowed to perform this action."));
        }
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function () {
            // Get id param
            $request = \Yii::$app->getRequest();
            $id = $request->get('id');

            $query = FormRule::find()->where('form_id=:form_id', [':form_id' => $id]);

            return new ActiveDataProvider([
                'query' => $query,
            ]);
        };

        return $actions;
    }
}
