<?php

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
/* @var $message string Custom Message */
/* @var $fields array Submission Fields */

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
</style>
    <div style="margin-bottom: 20px; font-size:14px; color: #222;">
    <?= strip_tags($message, '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>'); ?>
</div>

<?php if (isset($fields) && count($fields) > 0) : ?>

<table cellspacing="0" cellpadding="0">
    <tr style="background-color: #6e8292;">
        <th colspan="2" style="color:#ffffff; text-align: left; padding: 10px;">
            <?= Yii::t('app', 'Submission Details') ?>
        </th>
    </tr>
    <?php foreach ($fields as $label => $value) : ?>
    <tr style="background-color: <?=($i++%2==1) ? '#f3f5f7' : '#FFFFFF' ?>">
        <th style="vertical-align:top;color:#222;text-align:left;padding:7px 9px 7px 9px;border-top:1px solid #eee;">
            <?= $label ?>
        </th>
        <td style="vertical-align:top;color:#333;width:60%;padding:7px 9px 7px 0;border-top:1px solid #eee;">
            <div><?= $value ?></div>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>