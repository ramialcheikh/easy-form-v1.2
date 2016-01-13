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

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\helpers\ArrayHelper;

/**
 * FormBuilder is the form behind FormData Model.
 */
class FormBuilder extends Model
{
    public $data;
    public $html;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['data', 'required'],
            ['html', 'required'],
        ];
    }

    /**
     * Check if a email field is in the fields array
     * @param $emailField
     * @param $fields
     * @return bool
     */
    public function hasSameEmailField($emailField, $fields)
    {
        $emailFields = ArrayHelper::filter($fields, 'email', 'type');
        $emailsArray = ArrayHelper::column($emailFields, 'label', 'name');
        return array_key_exists($emailField, $emailsArray);
    }
}
