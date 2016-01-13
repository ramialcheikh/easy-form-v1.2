<?php

use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
/* @var $fields array Submission Fields */
/* @var $formID integer Form ID */
/* @var $submissionID integer Submission ID */
/* @var $message string Custom Message */

?>
<?= Yii::t('app', 'Submission Details') ?>:

<?php foreach ($fields as $label => $value) : ?>
    - <?= $label ?>: <?= $value ?>
<?php endforeach; ?>

<?= Yii::t('app', 'For more details') ?>,
<?= Yii::t('app', 'please go here') ?>:
<?= Url::to(['form/submissions', 'id' => $formID, '#' => 'view/' . $submissionID ], true) ?>
