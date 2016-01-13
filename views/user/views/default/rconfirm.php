<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\modules\user\models\forms\ForgotForm $model
 */

$this->title = Yii::t('app', 'Registration Confirmation');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-default-forgot">

    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
            <div class="form-wrapper">
                <?php if ($flash = Yii::$app->session->getFlash('Register-success')) : ?>

                    <div class="well">
                        <p class="text-success"><?= $flash ?> <p><?= Html::a(Yii::t("app", "Click Here"), ["/user/login"]) ?> to login.</p></p>
                    </div>         

                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
