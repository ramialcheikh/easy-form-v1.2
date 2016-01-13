<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use Carbon\Carbon;
use app\components\widgets\ActionBar;

$user = Yii::$app->getModule("user")->model("User");
$role = Yii::$app->getModule("user")->model("Role");

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\user\models\search\UserSearch $searchModel
 * @var app\modules\user\models\User $user
 * @var app\modules\user\models\Role $role
 */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;

Carbon::setLocale(substr(Yii::$app->language, 0, 2)); // eg. en-US to en
?>
<?php
    $gridColumns = [
        [
            'class' => '\kartik\grid\SerialColumn',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        'username',
        'email:email',
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'status',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
        ],
        'profile.company',
        [
            'attribute' => 'role_id',
            'label' => Yii::t('app', 'Role'),
            'filter' => $role::dropdown(),
            'value' => function ($model) use ($role) {
                $roleDropdown = $role::dropdown();
                return $roleDropdown[$model->role_id];
            },
        ],
        [
            'attribute'=> 'login_time',
            'value' => function ($model) {
                if (isset($model->login_time)) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $model->login_time)->diffForHumans();
                }
                return '';
            },
            'label' => Yii::t('app', 'Last login'),
        ],
        [
            'attribute'=> 'create_time',
            'value' => function ($model) {
                if (isset($model->create_time)) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $model->create_time)->diffForHumans();
                }
                return '';
            },
            'label' => Yii::t('app', 'Registered'),
        ],
        ['class' => '\kartik\grid\ActionColumn',
            'dropdown'=>true,
            'dropdownButton' => ['class'=>'btn btn-primary'],
            'dropdownOptions' => ['class' => 'pull-right'],
            'buttons' => [
                //view button
                'view' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'View'),
                        'aria-label' => Yii::t('app', 'View'),
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'. Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' .
                        Yii::t('app', 'View Record'), $url, $options).'</li>';
                },
                //update button
                'update' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Update'),
                        'aria-label' => Yii::t('app', 'Update'),
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'.Html::a('<span class="glyphicon glyphicon-pencil"></span> ' .
                        Yii::t('app', 'Update'), $url, $options).'</li>';
                },
                //delete button
                'delete' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Delete'),
                        'aria-label' => Yii::t('app', 'Delete'),
                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete this user? All data related to this item will be deleted. This action cannot be undone.'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'.Html::a('<span class="glyphicon glyphicon-bin"></span> ' .
                        Yii::t('app', 'Delete'), $url, $options).'</li>';
                },
            ],
        ],
    ];
?>

<div class="user-index">

    <?= GridView::widget([
        'id' => 'user-grid',
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'resizableColumns' => false,
        'pjax' => false,
        'export' => false,
        'responsive' => true,
        'bordered' => false,
        'striped' => true,
        'panelTemplate' => '<div class="panel {type}">
            {panelHeading}
            {panelBefore}
            {items}
            <div style="text-align: center">{pager}</div>
        </div>',
        'panel'=>[
            'type'=>GridView::TYPE_INFO,
            'heading'=> Yii::t('app', 'Users') . '<small class="panel-subtitle hidden-xs">'.
                Yii::t('app', 'Securely Access to each Form').'</small>',
            'footer'=>false,
            'before'=>
                ActionBar::widget([
                    'grid' => 'user-grid',
                    'templates' => [
                        '{create}' => ['class' => 'col-xs-6'],
                        '{help}' => ['class' => 'col-xs-6'],
                    ],
                    'elements' => [
                        'create' => Html::a(
                            '<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create User'),
                            ['create'],
                            ['class' => 'btn btn-primary']
                        ),
                        'help' => '<div class="pull-right">' .
                            $this->render('_search', ['model' => $searchModel]) . '</div>'
                    ],
                    'class' => 'form-control',
                ]),
        ],
        'toolbar' => false,
    ]); ?>

</div>
<?php
$js = <<< 'SCRIPT'

$(function () {
    $("[data-toggle='tooltip']").tooltip();
});;

SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);