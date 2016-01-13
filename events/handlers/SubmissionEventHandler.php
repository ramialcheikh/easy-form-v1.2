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

namespace app\events\handlers;

use Yii;
use yii\base\Component;
use app\helpers\MailHelper;

/**
 * Class SubmissionEvent
 * @package app\events
 */
class SubmissionEventHandler extends Component
{

    /**
     * Executed when a submission is received
     *
     * @param $event
     */
    public static function onSubmissionReceived($event)
    {
    }

    /**
     * Executed when a submission is accepted
     *
     * @param $event
     * @return bool
     */
    public static function onSubmissionAccepted($event)
    {

        /** @var \app\models\Form $formModel */
        $formModel = $event->form;
        /** @var \app\models\FormSubmission $formSubmissionModel */
        $formSubmissionModel = $event->submission;
        /** @var \app\models\FormData $formDataModel */
        $formDataModel = $formModel->formData;
        /** @var array $submissionData */
        $submissionData = $formSubmissionModel->getSubmissionData();
        /** @var array $filePaths */
        $filePaths = $event->filePaths;

        /** @var \app\components\queue\MailQueue $mailer */
        $mailer = Yii::$app->mailer;

        // Sender by default: No-Reply Email
        $fromEmail = MailHelper::from(Yii::$app->settings->get("app.noreplyEmail"));

        // Sender verification
        if (empty($fromEmail)) {
            return false;
        }

        /*******************************
        /* Send Notification by e-mail
        /*******************************/

        $formEmailModel = $formModel->formEmail;

        // Submission data in email format
        $fieldsForEmail = $formDataModel->getFieldsForEmail();
        $fields = array();

        foreach ($submissionData as $key => $value) {
            $fields[$fieldsForEmail[$key]] = $value;
        }

        // Data
        $data = [
            'fields' => $fields,
            'formID' => $formModel->id,
            'submissionID' => isset($formSubmissionModel->primaryKey) ? $formSubmissionModel->primaryKey : null,
            'message' => $formEmailModel->message,
        ];

        // Check first: Recipient and Sender are required
        if (isset($formEmailModel->to) && isset($formEmailModel->from) &&
            !empty($formEmailModel->to) && !empty($formEmailModel->from)) {

            // Views
            $notificationViews = $formEmailModel->getEmailViews();
            // Subject
            $subject = isset($formEmailModel->subject) && !empty($formEmailModel->subject) ?
                $formEmailModel->subject :
                $formModel->name . ' - ' . Yii::t('app', 'New Submission');
            // ReplyTo (can be an email or an email field)
            $replyTo = $formEmailModel->fromIsEmail() ? $formEmailModel->from :
                Yii::$app->request->post($formEmailModel->from);

            // Compose email
            /** @var \app\components\queue\Message $mail */
            $mail = Yii::$app->mailer->compose($notificationViews, $data)
                ->setFrom($fromEmail)
                ->setTo($formEmailModel->to)
                ->setReplyTo($replyTo)
                ->setSubject($subject);

            // Attach files
            if ($formEmailModel->attach && count($filePaths) > 0) {
                foreach ($filePaths as $filePath) {
                    $mail->attach(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . $filePath);
                }
            }

            // Send email to queue
            $mail->queue();
        }

        /*******************************
        /* Send Confirmation email
        /*******************************/

        $formConfirmationModel = $formModel->formConfirmation;

        // Check first: Send email must be active and Recipient is required
        if ($formConfirmationModel->send_email &&
            isset($formConfirmationModel->mail_to) && !empty($formConfirmationModel->mail_to)) {

            // To (Get value of email field)
            $to = Yii::$app->request->post($formConfirmationModel->mail_to);
            // Remove all illegal characters from email
            $to = filter_var($to, FILTER_SANITIZE_EMAIL);

            // Validate e-mail
            if (!filter_var($to, FILTER_VALIDATE_EMAIL) === false) {

                // Views
                $confirmationViews = $formConfirmationModel->getEmailViews();

                // Message
                $data = [
                    'message' => isset($formConfirmationModel->mail_message) &&
                    !empty($formConfirmationModel->mail_message) ? $formConfirmationModel->mail_message :
                        Yii::t('app', 'Thanks for your message'),
                ];

                // Add submission copy
                if ($formConfirmationModel->mail_receipt_copy) {
                    $data['fields'] = $fields;
                }

                // Subject
                $subject = isset($formConfirmationModel->mail_subject) && !empty($formConfirmationModel->mail_subject) ?
                    $formConfirmationModel->mail_subject : Yii::t('app', 'Thanks for your message');

                // ReplyTo
                $replyTo = isset($formConfirmationModel->mail_from) && !empty($formConfirmationModel->mail_from) ?
                    $formConfirmationModel->mail_from : Yii::$app->settings->get("app.noreplyEmail");

                // Add name to From
                if (isset($formConfirmationModel->mail_from_name) && !empty($formConfirmationModel->mail_from_name)) {
                    $replyTo = [
                        $replyTo => $formConfirmationModel->mail_from_name,
                    ];
                }

                // Compose email
                /** @var \app\components\queue\Message $mail */
                $mail = Yii::$app->mailer->compose($confirmationViews, $data)
                    ->setFrom($fromEmail)
                    ->setTo($to)
                    ->setReplyTo($replyTo)
                    ->setSubject($subject);
                // Send email to queue
                $mail->queue();
            }

        }
    }

    /**
     * Executed when a submission is rejected
     *
     * @param $event
     */
    public static function onSubmissionRejected($event)
    {
    }
}
