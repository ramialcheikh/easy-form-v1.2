<?php

use yii\helpers\Html;
use kartik\sidenav\SideNav;

/* @var $this yii\web\View */
/* @var $formModel app\models\Form */
/* @var $formDataModel app\models\FormData */

$this->title = $formModel->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Forms'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $formModel->name, 'url' => ['view', 'id' => $formModel->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Embed or Share');

?>
<div class="share-page">

    <div class="row">
        <div class="col-sm-3">
            <?php
            echo SideNav::widget([
                'type' => SideNav::TYPE_DEFAULT,
                'heading' => '<i class="glyphicon glyphicon-share-alt"></i> ' . Yii::t('app', 'Embed or Share'),
                'iconPrefix' => 'glyphicon glyphicon-',
                'items' => [
                    [
                        'url' => null,
                        'label' => Yii::t("app", "Embed Full Form"),
                        'icon' => 'embed',
                        'active' => true,
                        'options' => [
                            'id' => 'showEmbed',
                        ]
                    ],
                    [
                        'url' => null,
                        'label' => Yii::t("app", "Share Form Link"),
                        'icon' => 'paired',
                        'options' => [
                            'id' => 'showLink',
                        ]
                    ],
                ],
                'options' => [
                    'id' => 'sideNav',
                ]
            ]);
            ?>
        </div>
        <div class="col-sm-9">
            <div class="panel panel-default" id="embed">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-embed" style="margin-right: 5px;"></i>
                        <?= Yii::t("app", "Embed your form") ?>: <?= Html::encode($formModel->name) ?></h3>
                </div>
                <div class="panel-body">
                    <p><?= Yii::t("app", "You have multiple sharing options, the most used is the Embed Code which let you place a form on your website pages. There are two great options, with design or without design. Choose the best for you!") ?></p>
                    <p><?= Yii::t("app", "To share your form on your own site just use this embed code.") ?></p>
                    <?= $this->render('_embedForm', [
                        'formModel' => $formModel,
                        'formDataModel' => $formDataModel,
                    ]); ?>
                </div>
            </div>
            <div class="panel panel-default" id="link" style="display: none">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-paired" style="margin-right: 5px;"></i>
                        <?= Yii::t("app", "Share your form link") ?>: <?= Html::encode($formModel->name) ?></h3>
                </div>
                <div class="panel-body">
                    <p><?= Yii::t("app", "If you don’t want to embed the form in your website but you want to send  a link to your users, friends or coworkers then this is the option for you.") ?></p>
                    <p><strong><?= Yii::t("app", "Sharing has never been easier just copy the direct link below.") ?>
                        </strong></p>
                    <?= $this->render('_shareLink', [
                        'formModel' => $formModel,
                        'formDataModel' => $formDataModel,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$script = <<< JS
$( document ).ready(function(){
    $('#showEmbed').find('a').on('click', function(e) {
        e.preventDefault();
        $('#embed').show();
        $('#link').hide();
        $('#showEmbed').addClass('active');
        $('#showLink').removeClass('active');
    });
    $('#showLink').find('a').on('click', function(e) {
        e.preventDefault();
        $('#embed').hide();
        $('#link').show();
        $('#showEmbed').removeClass('active');
        $('#showLink').addClass('active');
    });
    $('#withoutDesign').change(function() {
        if($(this).is(":checked")) {
            $('#formUrl').val($('#formUrl').val() + '&t=0');
        } else {
            $('#formUrl').val($('#formUrl').val().replace('&t=0', ''));
        }
    });
    $('#withoutBox').change(function() {
        if($(this).is(":checked")) {
            $('#formUrl').val($('#formUrl').val() + '&b=0');
        } else {
            $('#formUrl').val($('#formUrl').val().replace('&b=0', ''));
        }
    });
    $('#withoutCustomJS').change(function() {
        if($(this).is(":checked")) {
            $('#formUrl').val($('#formUrl').val() + '&js=0');
        } else {
            $('#formUrl').val($('#formUrl').val().replace('&js=0', ''));
        }
    });
    $('#showForm').on('submit', function(e) {
        e.preventDefault();
        window.open($('#formUrl').val());
    });

    $('#withoutBoxAlt').change(function() {
        if($(this).is(":checked")) {
            $('#formUrlAlt').val($('#formUrlAlt').val() + '/0');
        } else {
            $('#formUrlAlt').val($('#formUrlAlt').val().slice(0,-2));
        }
    });
    $('#showFormAlt').on('submit', function(e) {
        e.preventDefault();
        window.open($('#formUrlAlt').val());
    });
})

JS;
$this->registerJs($script, $this::POS_END);
?>