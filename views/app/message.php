<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $message string */

?>
<div class="app-message">
    <?= Html::decode($message) ?>
</div>

<?php
// Utilities required for javascript
$this->registerJsFile('@web/static_files/js/form.utils.min.js', ['depends' => \yii\web\JqueryAsset::className()]);

$js = <<<JS
    jQuery(document).ready(function(){

        // Send the new height to the parent window
        Utils.postMessage({
            height: $("html").height()
        });

    });
JS;

$this->registerJs($js, $this::POS_END, 'message');

?>