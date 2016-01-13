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

namespace app\helpers;

use Yii;
use yii\console\Application;
use yii\web\ServerErrorHttpException;

/**
 * Class ConsoleHelper
 * @package app\helpers
 */
class ConsoleHelper
{
    /** @var Application $console Console Application */
    private static $console;

    /**
     * Running console command on background and get output
     *
     * @param string $cmd Argument that will be passed to console application
     * @return array [status, output]
     */
    public static function run($cmd)
    {
        $cmd = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php ' . Yii::getAlias('@app/yii') . ' ' . $cmd;
        $handler = null;
        if (self::isWindowsOS() === true) {
            $handler = popen('start ' . $cmd, 'r');

        } else {
            $handler = popen($cmd, 'r');
        }
        $output = '';
        while (!feof($handler)) {
            $output .= fgets($handler);
        }
        $output = trim($output);
        $status = pclose($handler);
        return [$status, $output];
    }

    /**
     * Running console command on background
     *
     * @param string $cmd Argument that will be passed to console application
     * @return int
     */
    public static function runOnBackground($cmd)
    {
        $cmd = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php ' . Yii::getAlias('@app/yii') . ' ' . $cmd;
        if (self::isWindowsOS() === true) {
            return pclose(popen('start /b ' . $cmd, 'r'));
        } else {
            return pclose(popen($cmd . ' > /dev/null &', 'r'));
        }
    }

    /**
     * Return console application
     *
     * @return Application
     * @throws ServerErrorHttpException
     */
    public static function console()
    {
        if (!self::$console) {

            $oldApp = Yii::$app;

            $consoleConfigFile = Yii::getAlias('@app/config/console.php');

            if (!file_exists($consoleConfigFile) || !is_array(($consoleConfig = require($consoleConfigFile)))) {
                throw new ServerErrorHttpException('Cannot find `'.
                    Yii::getAlias('@app/config/console.php').'`. Please create and configure console config.');
            }

            self::$console = new Application($consoleConfig);

            Yii::$app = $oldApp;
        }

        return self::$console;
    }

    /**
     * Run up migrations in background
     *
     * @param $migrationPath
     * @param $migrationTable
     */
    public static function migrate($migrationPath, $migrationTable)
    {
        self::runOnBackground('migrate --migrationPath=' . $migrationPath . ' --migrationTable=' . $migrationTable .
        ' --interactive=0');
    }

    /**
     * Run down migrations in background
     *
     * @param $migrationPath
     * @param $migrationTable
     */
    public static function migrateDown($migrationPath, $migrationTable)
    {
        self::runOnBackground('migrate/down --migrationPath=' . $migrationPath . ' --migrationTable=' .
            $migrationTable . ' --interactive=0');
    }

    /**
     * Checking is windows family OS
     *
     * @return boolean return true if script running under windows OS
     */
    protected static function isWindowsOS()
    {
        return strncmp(PHP_OS, 'WIN', 3) === 0;
    }
}
