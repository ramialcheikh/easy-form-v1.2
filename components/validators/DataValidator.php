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

namespace app\components\validators;

use Yii;
use app\models\FormData;
use app\models\FormSubmission;
use yii\web\UploadedFile;
use yii\validators\RequiredValidator;
use yii\validators\RegularExpressionValidator;
use yii\validators\UrlValidator;
use yii\validators\DateValidator;
use yii\validators\StringValidator;
use yii\validators\EmailValidator;
use yii\validators\NumberValidator;
use yii\validators\FileValidator;

/**
 * Class DataValidator
 * @package app\components\validators
 */
class DataValidator
{

    /*
     * Protected properties
     */

    // Arrays
    protected $data;                // FormSubmission Model Data property
    /** @var null|FormData @ */
    protected $dataModel;           // FormData Model
    protected $fields;              // All Fields
    protected $radioValues;         // All Radio Buttons Values
    protected $optionValues;        // All Option Values
    protected $checkboxValues;      // All Checkboxes Values
    protected $requiredLabels;      // Labels of Required Fields
    protected $uniqueFields;        // All Unique Fields
    protected $requiredFileLabels;  // All Required File Labels

    /*
     * Private properties
     */
    private $errors;

    /**
     * @param FormSubmission $submissionModel
     * @throws \Exception
     */
    public function __construct(FormSubmission $submissionModel)
    {

        if (!isset($submissionModel->form_id) || !is_array($submissionModel->data)) {
            throw new \Exception(Yii::t("app", "DataValidator needs the Form ID and Data attributes."));
        }

        $this->data = $submissionModel->data;

        $this->dataModel = FormData::findOne(['form_id' => $submissionModel->form_id]);
        $this->fields = $this->dataModel->getFields();
        $this->uniqueFields = $this->dataModel->getUniqueFields();
        $this->requiredFileLabels = $this->dataModel->getRequiredFileLabels();
        $this->requiredLabels = $this->dataModel->getRequiredLabels();
        $this->checkboxValues = $this->dataModel->getCheckboxValues();
        $this->radioValues = $this->dataModel->getRadioValues();
        $this->optionValues = $this->dataModel->getOptionValues();

    }

    /**
     * Validates a data array.
     */
    public function validate()
    {

        // Unique Fields Validation
        $this->uniqueFieldsValidation();
        // Required Fields Validation
        $this->requiredFieldsValidation();

        // Validation by input types
        $this->fieldTypeValidation();

    }

    public function uniqueFieldsValidation()
    {

        $message = Yii::t("app", "{label} '{value}' has already been taken.");

        foreach ($this->uniqueFields as $field) {
            // Only when the input value is not empty
            if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {

                // Strip whitespace from the beginning and end of a string
                $value = trim($this->data[$field["name"]]);

                // Search "fieldName":"fieldValue"
                $query = FormSubmission::find()
                    ->where('form_id=:form_id', [':form_id' => $this->dataModel->form_id])
                    ->andWhere(['like','data','"'.$field["name"].'":"'.$value.'"']);

                if ($query->count() > 0) {
                    $this->addError($field["name"], $field["label"], $value, $message);
                }

            }
        }
    }

    /**
     * The required validation works in 3 steps:
     * 1. Get all required fields that are not in the submission (data and files). If there are, at least one,
     *    the validation fail.
     * 2. Get all required files. If a required file if not in the global $_FILES, the validation fail.
     * 3. Get all required fields that pass the last steps, and validates them with a RequiredValidator.
     */
    public function requiredFieldsValidation()
    {

        // Messages
        $requiredMessage = Yii::t('app', 'the input value is required.');

        // Compares requiredLabels keys against data and files keys, and returns the difference
        // All requiredLabel that are not in the submission data and file uploads
        $requiredFields = array_diff_key($this->requiredLabels, $this->data, $_FILES);

        // If exist a requiredField, add a required error
        // Useful to validate if a least one checkbox of a group is checked
        if (count($requiredFields) > 0) {
            foreach ($requiredFields as $name => $label) {
                $this->addError($name, $label, '', $requiredMessage);
            }
        }

        // Check all required File Inputs with $_FILES
        foreach ($this->requiredFileLabels as $name => $label) {
            if (!is_array($_FILES) || !isset($_FILES[$name]) ||
                 !isset($_FILES[$name]['name']) || empty($_FILES[$name]['name'])) {
                // If no file was upload
                $this->addError($name, $label, '', $requiredMessage);
            }
        }

        // Get all submission data, that were not in the last validations
        $data = array_diff_key($this->data, $requiredFields, $this->requiredFileLabels);
        // Filter required data
        $requiredData = array_intersect_key($data, $this->requiredLabels);
        // Check each fields item with requiredValidator
        $requiredValidator = new RequiredValidator();
        // If a field does'nt pass the validator, add a blank error
        if (count($requiredData) > 0) {
            foreach ($requiredData as $name => $value) {
                if (!$requiredValidator->validate($value, $error)) {
                    $this->addError($name, $this->requiredLabels[$name], '', $error);
                }
            }
        }

    }

    public function fieldTypeValidation()
    {

        // Messages
        $invalidMessage = "the input value has a not valid value.";

        // Validation by Input Type

        foreach ($this->fields as $field) {
            foreach ($field as $key => $value) {
                // Text
                if ($key === "type" && $value === "text") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {
                        // A pattern can be used
                        if (isset($field["pattern"])) {
                            $regexValidator = new RegularExpressionValidator([
                                'pattern' => $field["pattern"]
                            ]);
                            if (!$regexValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error);
                            }
                        }
                    }
                }
                // Tel
                if ($key === "type" && $value === "tel") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {
                        // A pattern can be used
                        if (isset($field["pattern"])) {
                            $regexValidator = new RegularExpressionValidator([
                                'pattern' => $field["pattern"]
                            ]);
                            if (!$regexValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error);
                            }
                        } else {
                            // By default, the number must be a international phone number
                            $phoneValidator = new PhoneValidator();
                            if (!$phoneValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error . ' ' .
                                Yii::t("app", "It must has a internationally-standardized format
                                (e.g. '+1 650-555-5555')"));
                            }
                        }
                    }
                }
                // Url
                if ($key === "type" && $value === "url") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {
                        // Config validator
                        $config = [];
                        // A pattern can be used
                        if (isset($field["pattern"])) {
                            $config['pattern'] = $field["pattern"];
                        }
                        $urlValidator = new UrlValidator($config);
                        if (!$urlValidator->validate($this->data[$field["name"]], $error)) {
                            $this->addError($field["name"], $field["label"], '', $error);
                        }
                    }
                }
                // Color
                if ($key === "type" && $value === "color") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {
                        // hex color invalid
                        if (!preg_match('/^#[a-f0-9]{6}$/i', $this->data[$field["name"]])) {
                            $this->addError($field["name"], $field["label"], '', $invalidMessage .' '.
                                Yii::t("app", "It must be a hexadecimal color string (e.g. '#FFFFFF')."));
                        }
                    }
                }
                // Password
                if ($key === "type" && $value === "password") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {
                        $newData = trim($this->data[$field["name"]]); // Remove spaces
                        $stringValidator = new StringValidator([
                            'min' => 6, // Minimum length
                        ]);
                        if (!$stringValidator->validate($newData, $error)) {
                            $this->addError($field["name"], $field["label"], '', $error);
                        }
                        // A pattern can be used
                        if (isset($field["pattern"])) {
                            $regexValidator = new RegularExpressionValidator([
                                'pattern' => $field["pattern"]
                            ]);
                            if (!$regexValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error);
                            }
                        }
                    }
                }
                // Email
                if ($key === "type" && $value === "email") {

                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {

                        // Config email validator
                        $config = [];

                        // A pattern can be used
                        if (isset($field["pattern"])) {
                            $config['pattern'] = $field["pattern"];
                        }

                        // Whether to check if email's domain exists and has either an A or MX record.
                        // Be aware that this check can fail due temporary DNS problems
                        // even if the email address is valid and an email would be deliverable
                        if (isset($field["data-check-dns"])) {
                            $config['checkDNS'] = true;
                        }

                        // Validate multiple emails separated by commas
                        if (isset($field["multiple"])) {
                            // Removes spaces
                            $emails = str_replace(" ", "", $this->data[$field["name"]]);
                            // Array of emails
                            $emails = explode(",", $emails);
                            if (count($emails) > 1) {
                                $config['message'] = Yii::t('app', '{attribute} has a invalid email format: Please use a comma to separate multiple email addresses.');
                            }
                            // Validate only one email address
                            $emailValidator = new EmailValidator($config);
                            foreach ($emails as $email) {
                                if (!$emailValidator->validate($email, $error)) {
                                    $this->addError($field["name"], $field["label"], '', $error);
                                }
                            }
                        } else {
                            // Validate only one email address
                            $emailValidator = new EmailValidator($config);

                            if (!$emailValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error);
                            }

                        }
                    }
                }
                // Radio
                if ($key === "type" && $value === "radio") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && !empty($this->data[$field["name"]])) {
                        // If no values or if the received data does not match with the form data
                        if (empty($this->radioValues) || !in_array($this->data[$field["name"]], $this->radioValues)) {
                            $this->addError($field["name"], $field["groupLabel"], '', $invalidMessage);
                        }
                    }
                }
                // Checkbox
                if ($key === "type" && $value === "checkbox") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && !empty($this->data[$field["name"]])) {
                        // If no values or if the received data does not match with the form data
                        foreach ($this->data[$field["name"]] as $labelChecked) {
                            if (empty($this->checkboxValues) || !in_array($labelChecked, $this->checkboxValues)) {
                                $this->addError($field["name"], $field["groupLabel"], '', $invalidMessage);
                            }
                        }
                    }
                }
                // Select List
                if ($key === "tagName" && $value === "select") {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && !empty($this->data[$field["name"]])) {
                        // If no labels or if the received data does not match with the form data
                        foreach ($this->data[$field["name"]] as $optionSelected) {
                            if (empty($this->optionValues) || !in_array($optionSelected, $this->optionValues)) {
                                $this->addError($field["name"], $field["label"], '', $invalidMessage);
                            }
                        }
                    }
                }
                // Number & Range
                if (($key === "type" && $value === "number") || ($key === "type" && $value === "range")) {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {

                        // Config number validator
                        $config = [];
                        // Min Number Validation (Minimum value required)
                        if (isset($field["min"])) {
                            $config['min'] = $field["min"];
                        }

                        // Max Number Validation (Maximum value required)
                        if (isset($field["max"])) {
                            $config['max'] = $field["max"];
                        }

                        // Only Integer Validation (Whether the attribute value can only be an integer)
                        if (isset($field["data-integer-only"])) {
                            $config['integerOnly'] = true;
                        }

                        // Pattern to Validate only Integer Numbers (The regular expression for matching integers)
                        if (isset($field["data-integer-pattern"])) {
                            $config['integerPattern'] = $field["data-integer-pattern"];
                        }

                        // Pattern to Validate the Number (The regular expression for matching numbers)
                        if (isset($field["data-number-pattern"])) {
                            $config['numberPattern'] = $field["data-number-pattern"];
                        }

                        $numberValidator = new NumberValidator($config);

                        if (!$numberValidator->validate($this->data[$field["name"]], $error)) {
                            $this->addError($field["name"], $field["label"], '', $error);
                        }
                    }
                }
                // Date & DateTime & Time & Month & Week
                if (($key === "type" && $value === "date") || ($key === "type" && $value === "datetime-local") ||
                    ($key === "type" && $value === "time") || ($key === "type" && $value === "month") ||
                    ($key === "type" && $value === "week") ) {
                    // Only when the input value is not empty
                    if (isset($field["name"]) && (trim($this->data[$field["name"]]) !== "")) {

                        // DateValidator Configuration array
                        $config = [];

                        // Date Format by default
                        $format = "Y-m-d";
                        // Change Format
                        if ($value === "datetime-local") {
                            // DateTime Format
                            $format = "Y-m-d\TH:i:s";
                        } elseif ($value === "time") {
                            // Time Format
                            $format = "i:s";
                        } elseif ($value === "month") {
                            // Month Format
                            $format = "Y-m";
                        } elseif ($value === "week") {
                            // First, validate by regular expression
                            $regexValidator = new RegularExpressionValidator([
                                'pattern' =>"/\d{4}-W\d{2}/"
                            ]);
                            if (!$regexValidator->validate($this->data[$field["name"]], $error)) {
                                $this->addError($field["name"], $field["label"], '', $error);
                            }
                            // Next, convert to date, to dateValidator (min / max)
                            if (isset($field["min"])) {
                                $config['tooSmall'] = Yii::t("app", "{attribute} must be no less than {weekMin}.", [
                                    'weekMin' => $field["min"],
                                ]);
                                $field["min"] = date("Y-m-d", strtotime($field["min"]));
                            }
                            if (isset($field["max"])) {
                                $config['tooBig'] = Yii::t("app", "{attribute} must be no greater than {weekMax}.", [
                                    'weekMax' => $field["max"],
                                ]);
                                $field["max"] = date("Y-m-d", strtotime($field["max"]));
                            }
                            $this->data[$field["name"]] = date("Y-m-d", strtotime($this->data[$field["name"]]));
                        }

                        // Add PHP format
                        $config['format'] = "php:".$format;

                        // Add Min Date Validation (The value must be later than this option)
                        if (isset($field["min"])) {
                            $config['min'] = $field["min"];
                        }

                        // Add Max Date Validation (The value must be earlier than this option)
                        if (isset($field["max"])) {
                            $config['max'] = $field["max"];
                        }

                        $dateValidator = new DateValidator($config);

                        if (!$dateValidator->validate($this->data[$field["name"]], $error)) {
                            $this->addError($field["name"], $field["label"], '', $error);
                        }
                    }
                }
                // File
                if ($key === "type" && $value === "file") {
                    // Only when the $_FILES name value is not empty
                    if (isset($field["name"]) && isset($_FILES[$field["name"]]['name']) &&
                        !empty($_FILES[$field["name"]]['name'])) {

                        // Config FileValidator
                        $config = [];

                        // File type validation
                        // Note that you should enable fileinfo PHP extension.
                        if (isset($field["accept"]) && extension_loaded('fileinfo')) {
                            // Removes dots
                            $extensions = str_replace(".", "", $field["accept"]);
                            // Removes spaces
                            $extensions = str_replace(" ", "", $extensions);
                            $config['extensions'] = explode(",", $extensions);
                        }

                        // File Min Size validation
                        if (isset($field["data-min-size"])) {
                            // Removes dots
                            $config['minSize'] = (int) $field["data-min-size"];
                        }

                        // File Min Size validation
                        if (isset($field["data-max-size"])) {
                            // Removes dots
                            $config['maxSize'] = (int) $field["data-max-size"];
                        }

                        $file = UploadedFile::getInstanceByName($field["name"]);

                        $fileValidator = new FileValidator($config);

                        if (!$fileValidator->validate($file, $error)) {
                            $this->addError($field["name"], $field["label"], '', $error);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns a value indicating is there are any validation error.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return boolean is there are any error.
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->errors) : isset($this->errors[$attribute]);
    }

    /**
     * Returns errors for all attribute or single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ~~~
     * [
     *     'inputtext-0' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'inputtext-1' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ~~~
     *
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->errors === null ? [] : $this->getUniqueErrors();
        } else {
            return isset($this->errors[$attribute]) ? $this->errors[$attribute] : [];
        }
    }

    /**
     * Adds a new error to the specified attribute.
     *
     * @param $attribute
     * @param string $label
     * @param string $value
     * @param string $error
     */
    public function addError($attribute, $label = '', $value = '', $error = '')
    {
        if (!empty($label) && !empty($value)) {
            $this->errors[$attribute][] = Yii::t('app', $error, [
                'label' => $label,
                'value' => $value,
            ]);
        } else {
            $this->errors[$attribute][] = str_replace(Yii::t('app', 'the input value'), $label, $error);
        }
    }

    public function getUniqueErrors()
    {
        $errors = [];
        foreach ($this->errors as $key => $value) {
            $errors[$key] = array_unique($value);
        }
        return $errors;
    }
}
