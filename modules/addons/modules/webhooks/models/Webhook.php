<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.0
 * @author Balu
 * @copyright Copyright (c) 2015 Baluart.COM
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link http://easyforms.baluart.com/ Easy Forms
 */

namespace app\modules\addons\modules\webhooks\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Form;

/**
 * This is the model class for table "{{%addon_webhooks}}".
 *
 * @property integer $id
 * @property integer $form_id
 * @property string $url
 * @property string $handshake_key
 * @property integer $status
 * @property integer $json
 *
 * @property \app\models\Form $form
 */
class Webhook extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%addon_webhooks}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['form_id', 'url'], 'required'],
            [['form_id', 'status', 'json'], 'integer'],
            [['url'], 'string', 'max' => 2083],
            [['handshake_key'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'form_id' => Yii::t('app', 'Form'),
            'url' => Yii::t('app', 'Url'),
            'handshake_key' => Yii::t('app', 'Handshake Key'),
            'status' => Yii::t('app', 'Status'),
            'json' => Yii::t('app', 'Json'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm()
    {
        return $this->hasOne(Form::className(), ['id' => 'form_id']);
    }
}