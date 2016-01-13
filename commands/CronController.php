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

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\components\cron\CronExpression;

/**
 * Class CronController
 *
 * @package app\commands
 */
class CronController extends Controller
{

    /**
     * @var string the default command action.
     */
    public $defaultAction = 'run';

    /**
     * @var string path to yii runner script.
     */
    public $yiiPath;

    /**
     * Save output in log file
     * @var bool
     */
    public $saveLog = false;

    /**
     * Update or rewrite log file
     * False - rewrite True - update(add to end logs)
     * @var bool
     */
    public $updateLogFile = true;

    /**
     * @var string directory to writing logs
     */
    public $logsDir = 'runtime/logs/';

    public function init()
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            $this->saveLog = true;
        }

        $this->yiiPath = Yii::getAlias('@app') . '/yii';
    }

    /**
     * Run cron commands
     *
     * @return int
     */
    public function actionRun()
    {
        // NOTE: Linux Cron must be configured to every minute, no less

        // By default, update analytics every day
        $cron = CronExpression::factory(Yii::$app->params['App.Analytics.cronExpression']);
        if ($cron->isDue()) {
            // Update analytics
            $this->runCommand("php " . $this->yiiPath . " analytics");
        }

        // By default, process mail queue every minute
        $cron = CronExpression::factory(Yii::$app->params['App.Mailer.cronExpression']);
        if ($cron->isDue()) {
            // Process queue
            $this->runCommand("php " . $this->yiiPath . " queue");
        }

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * OS-independent background command execution.
     *
     * @param $command
     * @param string $logFileName filename for writing stdout
     */
    protected function runCommand($command, $logFileName = 'cron.log')
    {
        // Save stdout
        if ($this->saveLog) {
            $concat = ($this->updateLogFile) ? '>>' : '>';
            $logPath = Yii::getAlias('@app') . '/' . $this->logsDir . '/' . $logFileName;
            $command = $command . ' '. $concat . escapeshellarg($logPath) .' 2>&1';
        }
        // Run command
        if ($this->isWindowsOS()) {
            //Windows OS
            pclose(popen('start /B "Yii run command" '.$command, 'r'));
        } else {
            //nix based OS
            system($command.' &');
        }
    }

    /**
     * Checking is windows family OS
     *
     * @return boolean return true if script running under windows OS
     */
    protected function isWindowsOS()
    {
        return strncmp(PHP_OS, 'WIN', 3) === 0;
    }
}
