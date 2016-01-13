<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

$this->title = Yii::t('app', 'Site settings');

$this->params['breadcrumbs'][] = ['label' => $this->title];

?>
<div class="account-management">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-cogwheels" style="margin-right: 5px;"></i>
                <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(); ?>
            <?php
            /* @var $settings */
            foreach ($settings as $index => $setting) {
                if ($setting->key == "name") {
                    echo "<div class='row'><div class='col-sm-12'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "Name"));
                    echo "</div></div>";
                } elseif ($setting->key == "description") {
                    echo "<div class='row'><div class='col-sm-12'>";
                    echo $form->field($setting, "[$index]value")->textArea(['rows' => '3'])
                        ->label(Yii::t("app", "Description"));
                    echo "</div></div>";
                } elseif ($setting->key == "adminEmail") {
                    echo "<div class='row'><div class='col-sm-4'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "Admin e-mail"));
                    echo "</div>";
                } elseif ($setting->key == "supportEmail") {
                    echo "<div class='col-sm-4'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "Support e-mail"));
                    echo "</div>";
                } elseif ($setting->key == "noreplyEmail") {
                    echo "<div class='col-sm-4'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "No-Reply e-mail"));
                    echo "</div></div>";
                } elseif ($setting->key == "reCaptchaSecret") {
                    echo "<div class='row'><div class='col-sm-6'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "ReCaptcha Secret Key"))
                        ->hint(Yii::t(
                            "app",
                            "Used for communications between your site and Google. Be careful not to disclose it to anyone."
                        ));
                    echo "</div>";
                } elseif ($setting->key == "reCaptchaSiteKey") {
                    echo "<div class='col-sm-6'>";
                    echo $form->field($setting, "[$index]value")->label(Yii::t("app", "ReCaptcha Site Key"))
                        ->hint(Yii::t("app", "Used in the HTML code that displays your forms to your users.") .
                            " <a href='https://www.google.com/recaptcha' target='_blank'>".
                            Yii::t("app", "Get your keys.") ."</a>");
                    echo "</div></div>";
                } else {
                    echo "<div class='row'><div class='col-sm-12'>";
                    echo $form->field($setting, "[$index]value")->label($setting->key);
                    echo "</div></div>";
                }
            }
            ?>
            <div class="col-sm-12">
                <div class="form-group" style="text-align: right; margin-top: 10px">
                    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
ActiveForm::end();