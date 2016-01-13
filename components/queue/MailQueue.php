<?php
/**
 * MailQueue.php
 * @author Saranga Abeykoon http://nterms.com
 */

namespace app\components\queue;

use Yii;
use yii\db\Expression;
use yii\swiftmailer\Mailer;
use app\models\Queue;

/**
 * MailQueue is a sub class of yii\switmailer\Mailer
 * which intends to replace it.
 *
 * Configuration is the same as in `yii\switmailer\Mailer` with some additional properties to control the mail queue
 *
 * @see http://www.yiiframework.com/doc-2.0/yii-swiftmailer-mailer.html
 * @see http://www.yiiframework.com/doc-2.0/ext-swiftmailer-index.html
 *
 * This extension replaces `yii\switmailer\Message` with `app\components\queue\Message'
 * to enable queuing right from the message.
 */
class MailQueue extends Mailer
{
    const NAME = 'mailQueue';
    
    /**
     * @var string message default class name.
     */
    public $messageClass = 'app\components\queue\Message';

    /**
     * @var integer the default value for the number of mails to be sent out per processing round.
     */
    public $mailsPerRound = 10;
    
    /**
     * @var integer maximum number of attempts to try sending an email out.
     */
    public $maxAttempts = 3;

    /**
     * Sends out the messages in email queue and update the database.
     *
     * @return boolean true if all messages are successfully sent out
     */
    public function process()
    {
        $success = true;

        $items = Queue::find()
            ->where(['and', ['sent_time' => null], ['<', 'attempts', $this->maxAttempts]])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit($this->mailsPerRound)
            ->all();
        // dd($items);
        if (!empty($items)) {
            foreach ($items as $item) {
                /** @var \app\models\Queue $item */
                if ($message = $item->toMessage()) {
                    $attributes = ['attempts', 'last_attempt_time'];

                    if ($this->sendMessage($message)) {
                        // $item->sent_time = new Expression('NOW()');
                        // $attributes[] = 'sent_time';
                        $item->delete();
                    } else {
                        $success = false;
                    }

                    $item->attempts++;
                    $item->last_attempt_time = new Expression('NOW()');

                    $item->updateAttributes($attributes);
                }
            }
        }

        return $success;
    }
}
