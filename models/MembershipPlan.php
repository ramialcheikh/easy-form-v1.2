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

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "theme".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $color
 * @property string $css
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $author
 * @property User $lastEditor
 */
class MembershipPlan extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%membership_plan}}';
    }

  
  public function rules()
    {
        return [
//            [['user_id'], 'required'],
//            [['user_id'], 'integer'],
//            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 125],
			[['amount'], 'integer'],
			[['status'], 'integer'],
           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'plan_id' => Yii::t('app', 'Plan Id'),
            'title' => Yii::t('app', 'Title'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'status'),
           
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(User::className(), ['id' => 'plan_id']);
    }

  
}
