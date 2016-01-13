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

namespace app\modules\addons\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\helpers\ConsoleHelper;
use app\modules\addons\models\Addon;
use app\modules\addons\models\AddonSearch;

/**
 * DefaultController implements the CRUD actions for Addon model.
 */
class AdminController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * List of all Add-ons.
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {

        $this->refreshAddOnsList();

        $searchModel = new AddonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Reload index action
     */
    public function actionRefresh()
    {
        // Show success alert
        Yii::$app->getSession()->setFlash('success', Yii::t(
            'addon',
            'The Add-ons list has been refreshed successfully.'
        ));

        $this->redirect(['index']);
    }

    /**
     * Add / Remove Add-Ons automatically.
     *
     * @throws InvalidConfigException
     */
    protected function refreshAddOnsList()
    {

        // Absolute path to addOns directory
        $addOnsDirectory = Yii::getAlias('@addons');

        // Name of each sub-directory
        $subDirectories = array_diff(scandir($addOnsDirectory), array('..', '.'));

        // Each sub-directory name is an addOn ID
        $addOns = [];
        foreach ($subDirectories as $addOnID) {
            // Must be a directory
            if (is_dir($addOnsDirectory . DIRECTORY_SEPARATOR . $addOnID)) {
                array_push($addOns, $addOnID);
            }
        }

        $installedAddOns = ArrayHelper::map(Addon::find()->select(['id','id'])->asArray()->all(), 'id', 'id');
        $newAddOns = array_diff($addOns, $installedAddOns);
        $removedAddOns = array_diff($installedAddOns, $addOns);

        // Install new addOns
        foreach ($newAddOns as $newAddOn) {

            // Verify if Module.php file of the new addOn exist
            $file = $addOnsDirectory . DIRECTORY_SEPARATOR . $newAddOn . DIRECTORY_SEPARATOR . 'Module.php';

            if (!is_file($file)) {
                throw new InvalidConfigException(Yii::t(
                    'addon',
                    'An invalid Add-on detected. Please verify your Add-ons directory.'
                ));
            } else {
                $configFile = Yii::getAlias('@addons') . DIRECTORY_SEPARATOR . $newAddOn . DIRECTORY_SEPARATOR .
                    'config' . DIRECTORY_SEPARATOR . 'install.php';

                if (is_file($configFile)) {

                    $config = require($configFile);

                    if (!is_array($config) || !isset($config['id']) || !isset($config['name']) ||
                        !isset($config['class'])) {
                        throw new InvalidConfigException(Yii::t(
                            'addon',
                            'An invalid Add-on detected. Please verify your Add-On configuration.'
                        ));
                    }

                    // Add AddOn to List
                    $addOnModel = new Addon();
                    $addOnModel->id = $config['id'];
                    $addOnModel->name = $config['name'];
                    $addOnModel->class = $config['class'];
                    $addOnModel->description = isset($config['description']) &&
                    isset($config['description'][Yii::$app->language]) ?
                        $config['description'][Yii::$app->language] : null;
                    $addOnModel->version = isset($config['version']) ? $config['version'] : null;
                    $addOnModel->status = isset($config['status']) ? $config['status'] : null;
                    $addOnModel->save();
                }
            }
        }

        // Uninstall removed addOns
        foreach ($removedAddOns as $removedAddOn) {
            $addOnModel = Addon::find()->where(['id' => $removedAddOn])->one();
            $addOnModel->delete();
        }

    }

    /**
     * Enable / Disable multiple Add-ons
     *
     * @param $status
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdateStatus($status)
    {

        $addOns = Addon::findAll(['id' => Yii::$app->getRequest()->post('ids')]);

        if (empty($addOns)) {
            throw new NotFoundHttpException(Yii::t('addon', 'Page not found.'));
        } else {
            foreach ($addOns as $addOn) {
                $addOn->status = $status;
                $addOn->update();
            }
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('addon', 'The selected items have been successfully updated.')
            );
            return $this->redirect(['index']);
        }
    }

    /**
     * Run DB Migration Up
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionInstall()
    {
        $addOns = Addon::findAll(['id' => Yii::$app->getRequest()->post('ids')]);

        if (empty($addOns)) {
            throw new NotFoundHttpException(Yii::t('addon', 'Page not found.'));
        } else {
            foreach ($addOns as $addOn) {

                // Run Addon DB Migration
                $migrationPath = Yii::getAlias('@addons') . DIRECTORY_SEPARATOR . $addOn->id . DIRECTORY_SEPARATOR .
                    'migrations';

                if (is_dir($migrationPath)) {
                    ConsoleHelper::migrate($migrationPath, 'migration_' . $addOn->id);
                }

                $addOn->status = $addOn::STATUS_ACTIVE;
                $addOn->installed = $addOn::INSTALLED_ON;
                $addOn->update();

            }
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('addon', 'The selected items have been installed successfully.')
            );
            return $this->redirect(['index']);
        }
    }

    /**
     * Run DB Migration Down
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUninstall()
    {
        $addOns = Addon::findAll(['id' => Yii::$app->getRequest()->post('ids')]);

        if (empty($addOns)) {
            throw new NotFoundHttpException(Yii::t('addon', 'Page not found.'));
        } else {
            foreach ($addOns as $addOn) {

                // Run Addon DB Migration
                $migrationPath = Yii::getAlias('@addons') . DIRECTORY_SEPARATOR . $addOn->id . DIRECTORY_SEPARATOR .
                    'migrations';

                if (is_dir($migrationPath)) {
                    ConsoleHelper::migrateDown($migrationPath, 'migration_' . $addOn->id);
                }

                $addOn->status = $addOn::STATUS_INACTIVE;
                $addOn->installed = $addOn::INSTALLED_OFF;
                $addOn->update();

            }
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t('addon', 'The selected items have been uninstalled successfully.')
            );
            return $this->redirect(['index']);
        }
    }
}
