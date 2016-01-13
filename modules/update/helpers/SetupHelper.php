<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.1
 * @author Balu
 * @copyright Copyright (c) 2015 Baluart.COM
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link http://easyforms.baluart.com/ Easy Forms
 */

namespace app\modules\update\helpers;

use Yii;
use app\helpers\ConsoleHelper;

class SetupHelper
{

    /**
     * Runs new migrations
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
            $result = ConsoleHelper::run("migrate/up $numberOfMigrations --interactive=0");
            $lines = explode(PHP_EOL, $result[1]);
            if ($lines[count($lines)-1] === "Migrated up successfully.") {
                return 1;
            }
        }

        return 0;
    }
}
