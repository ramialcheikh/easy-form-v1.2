<?php
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
/* @var $fields array Submission Fields */
/* @var $formID integer Form ID */
/* @var $submissionID integer Submission ID */
/* @var $message string Custom Message */

$i = 0; // Used in row background
?>

<style type="text/css">
    table {
        width: 100%;
        border-bottom: 1px solid #eee;
        font-size: 12px;
        line-height: 135%;
        font-family: 'Lucida Grande', 'Lucida Sans Unicode', Tahoma, sans-serif;
    }

    .th-top {
        color: #ffffff;
        text-align: left;
        padding: 10px;
    }

    .th-left {
        vertical-align: top;
        color: #222;
        text-align: left;
        padding: 7px 9px 7px 9px;
        border-top: 1px solid #eee;
    }
</style>

<table cellspacing="0" cellpadding="0">
        <tr style="background-color: #6e8292;">
            <th colspan="2" class="th-top"><?= Yii::t('app', 'Submission Details') ?></th>
        </tr>
        <?php foreach ($fields as $label => $value) : ?>
        <tr style="background-color: <?=($i++%2==1) ? '#f3f5f7' : '#FFFFFF' ?>">
            <th>
                <?= $label ?>
            </th>
            <td class="th-left">
                <div><?= $value ?></div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><?= Yii::t('app', 'For more details') ?>,
        <a href="<?= Url::to(['form/submissions', 'id' => $formID, '#' => 'view/' . $submissionID ], true) ?>">
            <?= Yii::t('app', 'please click here') ?>
        </a>.
    </p>
