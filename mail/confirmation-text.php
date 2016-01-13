<?php

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
/* @var $message string Custom Message */
/* @var $fields array Submission Fields */

?>

<?= strip_tags($message); ?>

<?php if (isset($fields) && count($fields) > 0) : ?>

    <?= Yii::t('app', 'Submission Details') ?>:

    <?php foreach ($fields as $label => $value) : ?>
        - <?= $label ?>: <?= $value ?>
    <?php endforeach; ?>

<?php endif; ?>
