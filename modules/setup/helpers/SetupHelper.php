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

namespace app\modules\setup\helpers;

use Yii;
use yii\helpers\VarDumper;
use app\helpers\ConsoleHelper;

class SetupHelper
{

    /**
     * Verify access to database config file
     *
     * @return bool
     */
    public static function checkDatabaseConfigFilePermissions()
    {
        $file = Yii::getAlias('@app/config/db.php');

        $result = touch($file); // Check access file

        return $result;
    }

    /**
     * Create database configuration content
     * that will be saved in a file
     *
     * @param $config
     * @return array
     */
    public static function createDatabaseConfig($config)
    {
        $config['class'] = 'yii\db\Connection';
        $config['dsn'] = 'mysql:host='.$config['db_host'].';port='.$config['db_port'].';dbname='.$config['db_name'];
        $config['username'] = $config['db_user'];
        $config['password'] = $config['db_pass'];
        unset(
            $config['db_name'],
            $config['db_host'],
            $config['db_port'],
            $config['db_user'],
            $config['db_pass'],
            $config['connectionOk']
        );
        return $config;
    }

    /**
     * Write database configuration content in a file
     *
     * @param $config
     * @return bool
     */
    public static function createDatabaseConfigFile($config)
    {
        $content = VarDumper::export($config);
        $content = preg_replace('~\\\\+~', '\\', $content); // Fix class backslash
        $content = "<?php\nreturn " . $content . ";\n";

        return file_put_contents(Yii::getAlias('@app/config/db.php'), $content) > 0;
    }

    /**
     * Runs migrations
     *
     * @param int $numberOfMigrations
     * @return int
     */
    public static function runMigrations($numberOfMigrations = null)
    {
        // Run DB Migration
        $migrationPath = Yii::getAlias('@app/migrations');

        if (is_dir($migrationPath)) {
            // Force migrate db without confirmation
            $result = ConsoleHelper::run("migrate $numberOfMigrations --interactive=0");
            $lines = explode(PHP_EOL, $result[1]);
            if ($lines[count($lines)-1] === "Migrated up successfully.") {
                return 1;
            }
        }

        return 0;
    }
}
