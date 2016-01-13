<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use Carbon\Carbon;
use yii\bootstrap\Dropdown;
use app\components\widgets\ActionBar;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\FormSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */
/* @var $templates array */

$this->title = Yii::t("app", "Forms");
$this->params['breadcrumbs'][] = $this->title;

// Prepare dropdown with templates array
$templateItems = array();

if (count($templates) > 0) {
    // Set data for dropdown widget
    foreach ($templates as $template) {
        $item = [
            'label' => $template['name'],
            'url' => Url::to(['create', 'template' => $template['slug']]),
        ];
        array_push($templateItems, $item);
    }
    $itemDivider = [
        'label' => '<li role="presentation" class="divider"></li>',
        'encode' => false,
    ];
    array_push($templateItems, $itemDivider);
}

// Add link to templates
$itemMoreTeplates = [
    'label' => Yii::t('app', 'More Templates'),
    'url' => Url::to(['/templates']),
];
array_push($templateItems, $itemMoreTeplates);

Carbon::setLocale(substr(Yii::$app->language, 0, 2)); // eg. en-US to en
?>
<?php

    $gridColumns = [
        [
            'class' => '\kartik\grid\CheckboxColumn',
            'headerOptions' => ['class'=>'kartik-sheet-style'],
            'rowSelectedClass' => GridView::TYPE_WARNING,
        ],
        [
            'attribute'=> 'name',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a(Html::encode($model->name), ['form/view', 'id' => $model->id]);
            },
        ],
        [
            'attribute'=>'language',
            'value'=> 'languageLabel',
        ],
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'status',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
        ],
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'save',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
        ],
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'honeypot',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
        ],
        [
            'attribute' => 'author',
            'value' => function ($model) {
                return isset($model->author, $model->author->username) ? Html::encode($model->author->username) : null;
            },
            'label' => Yii::t("app", "Created by")
        ],
        [
            'attribute'=> 'updated_at',
            'value' => function ($model) {
                return Carbon::createFromTimestampUTC($model->updated_at)->diffForHumans();
            },
            'label' => Yii::t('app', 'Updated'),
        ],
        ['class' => '\kartik\grid\ActionColumn',
            'controller' => 'form',
            'visible' => (!empty(Yii::$app->user) && Yii::$app->user->can("admin")), // Visible only for admin user
            'dropdown'=>true,
            'dropdownButton' => ['class'=>'btn btn-primary'],
            'dropdownOptions' => ['class' => 'pull-right'],
            'buttons' => [
                //update button
                'update' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-pencil"> </span> '. Yii::t('app', 'Update'),
                        $url,
                        ['title' => Yii::t('app', 'Update')]
                    ) .'</li>';
                },
                //settings button
                'settings' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-cogwheel"> </span> '. Yii::t('app', 'Settings'),
                        $url,
                        ['title' => Yii::t('app', 'Settings')]
                    ) .'</li>';
                },
                //rule button
                'rules' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-flowchart"> </span> '. Yii::t('app', 'Conditional Rules'),
                        $url,
                        ['title' => Yii::t('app', 'Conditional Rules')]
                    ) .'</li>';
                },
                //preview form button
                'view' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-eye-open"> </span> ' . Yii::t('app', 'View Record'),
                        $url,
                        ['title' => Yii::t('app', 'View Record')]
                    ) .'</li>';
                },
                //share form button
                'share' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-share"> </span> '. Yii::t('app', 'Publish & Share'),
                        $url,
                        ['title' => Yii::t('app', 'Publish & Share')]
                    ) .'</li>';
                },
                //form submissions button
                'submissions' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-send"> </span> '. Yii::t('app', 'Submissions'),
                        $url,
                        ['title' => Yii::t('app', 'Submissions')]
                    ) .'</li>';
                },
                //form report button
                'report' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-pie-chart"> </span> '. Yii::t('app', 'Submissions Report'),
                        $url,
                        ['title' => Yii::t('app', 'Submissions Report')]
                    ) .'</li>';
                },
                //form analytics button
                'analytics' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-charts"> </span> '. Yii::t('app', 'Form Analytics'),
                        $url,
                        ['title' => Yii::t('app', 'Form & Submissions Analytics')]
                    ) .'</li>';
                },
                //delete button
                'delete' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Delete'),
                        'aria-label' => Yii::t('app', 'Delete'),
                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete this form? All stats, submissions, conditional rules and reports data related to this item will be deleted. This action cannot be undone.'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-bin"> </span> '.
                        Yii::t('app', 'Delete'),
                        $url,
                        $options
                    ).'</li>';
                },
            ],
            'urlCreator' => function ($action, $model) {
                if ($action === 'update') {
                    $url = Url::to(['form/update', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "settings") {
                    $url = Url::to(['form/settings', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "rules") {
                    $url = Url::to(['form/rules', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "view") {
                    $url = Url::to(['form/view', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "share") {
                    $url = Url::to(['form/share', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "submissions") {
                    $url = Url::to(['form/submissions', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "report") {
                    $url = Url::to(['form/report', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "analytics") {
                    $url = Url::to(['form/analytics', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "delete") {
                    $url = Url::to(['form/delete', 'id' => $model->id]);
                    return $url;
                }
                return '';
            },
            'template' => '{update} {settings} {rules} {view} {share} {submissions} {report} {analytics} {delete}'
        ],
    ];

?>

<div class="form-index">
    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'id' => 'form-grid',
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
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
                'panel' => [
                    'type'=>GridView::TYPE_INFO,
                    'heading'=> Yii::t('app', 'Forms') .' <small class="panel-subtitle hidden-xs">'.
                        Yii::t('app', 'Build any type of online form').'</small>',
                    'footer'=>false,
                    // Visible only for admin user
                    'before'=> (!empty(Yii::$app->user) && Yii::$app->user->can("admin")) ?
                        ActionBar::widget([
                            'grid' => 'form-grid',
                            'templates' => [
                                '{create}' => ['class' => 'col-xs-6 col-md-8'],
                                '{bulk-actions}' => ['class' => 'col-xs-6 col-md-2 col-md-offset-2'],
                            ],
                            'elements' => [
                                'create' =>
                                    '<div class="btn-group">' .
                                        Html::a(
                                            '<span class="glyphicon glyphicon-plus"></span> ' .
                                            Yii::t('app', 'Create Form'),
                                            ['create'],
                                            ['class' => 'btn btn-primary']
                                        ) .
                                        '<button type="button" class="btn btn-primary dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>' .
                                        Dropdown::widget(['items' => $templateItems]) .
                                    '</div> ' .
                                    Html::a(Yii::t('app', 'Do you want to customize your forms?'), ['/theme'], [
                                        'data-toggle' => 'tooltip',
                                        'data-placement'=> 'top',
                                        'title' => Yii::t('app', 'No problem at all. With a theme, you can easily add custom CSS styles to your forms, to customize colors, field sizes, backgrounds, fonts, and more.'),
                                        'class' => 'text hidden-xs hidden-sm']),
                            ],
                            'bulkActionsItems' => [
                                Yii::t('app', 'Update Status') => [
                                    'status-active' => Yii::t('app', 'Active'),
                                    'status-inactive' => Yii::t('app', 'Inactive'),
                                ],
                                Yii::t('app', 'General') => ['general-delete' => Yii::t('app', 'Delete')],
                            ],
                            'bulkActionsOptions' => [
                                'options' => [
                                    'status-active' => [
                                        'url' => Url::toRoute(['update-status', 'status' => 1]),
                                    ],
                                    'status-inactive' => [
                                        'url' => Url::toRoute(['update-status', 'status' => 0]),
                                    ],
                                    'general-delete' => [
                                        'url' => Url::toRoute('delete-multiple'),
                                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete these forms? All stats, submissions, conditional rules and reports data related to each item will be deleted. This action cannot be undone.'),
                                    ],
                                ],
                                'class' => 'form-control',
                            ],

                            'class' => 'form-control',
                        ]) : null,
                ],
                'toolbar' => false
            ]); ?>
        </div>
    </div>
</div>
<?php
$js = <<< 'SCRIPT'

$(function () {
    $("[data-toggle='tooltip']").tooltip();
});

SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);