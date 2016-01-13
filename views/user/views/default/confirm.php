<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var bool $success
 */

$this->title = Yii::t('app', $success ? 'Confirmed' : 'Error');
?>
<div class="user-default-confirm">

    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
            <div class="form-wrapper">
                <div class="well">
                <?php if ($success) : ?>
                        <p class="text-success">
                            <?= Yii::t("app", "Your email [ {email} ] has been confirmed", ["email" => $success]) ?>
                        </p>

                    <?php if (Yii::$app->user->isLoggedIn) : ?>

                        <p><?= Html::a(Yii::t("app", "Go to my account"), ["/user/profile"]) ?></p>
                        <p><?= Html::a(Yii::t("app", "Go to Dashboard"), Yii::$app->getHomeUrl()) ?></p>

                    <?php else : ?>

                        <p><?= Html::a(Yii::t("app", "Log in here"), ["/user/login"]) ?></p>

                    <?php endif; ?>

                <?php else : ?>

                    <p class="text-danger">
                        <?= Yii::t("app", "Invalid confirmation key. The key may have expired.") ?>
                    </p>

                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>