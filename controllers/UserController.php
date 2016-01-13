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

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;

/**
 * Class UserController
 * @package app\controllers
 */
class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['confirm', 'resend'],
                        'allow'   => true,
                        'roles'   => ['?', '@'],
                    ],
                    [
                        'actions' => ['index', 'account', 'profile', 'change-username', 'change-email',
                            'change-password', 'avatar-delete', 'resend-change', 'cancel', 'logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['login', 'forgot', 'reset','register','rconfirm','pconfirm'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display index - debug page or profile page
     */
    public function actionIndex()
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            $actions = Yii::$app->getModule("user")->getActions();
            return $this->render('index', ["actions" => $actions]);
        } else {
            return $this->redirect(["/user/profile"]);
        }
    }

    /**
     * Display login page
     */
    public function actionLogin()
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\forms\LoginForm $model */

        // load post data and login
        $model = Yii::$app->getModule("user")->model("LoginForm");
		
        if ($model->load(Yii::$app->request->post()) && $model->login(Yii::$app->getModule("user")->loginDuration)) {
            return $this->goBack(Yii::$app->getModule("user")->loginRedirect);
        }

        // render
        return $this->render('login', [
            'model' => $model,
        ]);
    }
	
	 /**
     * Display login page
     */
    public function actionRegister()
    {
        $this->layout = 'public';
		
//return $this->goBack(Yii::$app->getModule("user")->rconfirmRedirect);
        /** @var \app\modules\user\models\forms\LoginForm $model */

        // load post data and login
		//$models = MembershipPlan::model()->findAll();
		
        $model = Yii::$app->getModule("user")->model("RegisterForm");
		
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
		  $arrData = Yii::$app->request->post();	
          if($arrData['RegisterForm']['roleid'] == 2){
			   // set flash (which will show on the current page)
            Yii::$app->session->setFlash(
                "Register-success",
                Yii::t("app", "The registration has been successfull. The password has been sent your registered email address. ")
            );
			  $this->redirect('rconfirm');
		  }else{
			  
			$price = array('1','1','2','5','10','15');
			$plan = array('1','1','2','Basic','Standard','Premium'); 
			
			
			$paypal_email = 'personaltest@matrimony.com';
			$return_url = 'http://localhost/qform/app/user/pconfirm?success=true';
			$cancel_url = 'http://localhost/qform/app/user/rconfirm?success=false';
			$notify_url = 'http://localhost/qform/app/user/rconfirm?success=false';
			$item_name = $plan[Yii::$app->session->get('user.planid')];
			$item_amount = $price[Yii::$app->session->get('user.planid')];
			
			// Firstly Append paypal account to querystring
			$querystring = "?business=".urlencode($paypal_email)."&";	
			
			// Append amount& currency (£) to quersytring so it cannot be edited in html
			
			//The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
			$querystring = $querystring."item_name=".urlencode($item_name)."&";
			$querystring = $querystring."amount=".urlencode($item_amount)."&";
			$arrData = array('cmd'=>'_xclick','no_note'=>'1','currency_code'=>'USD','bn'=>'PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest','item_number'=>Yii::$app->session->get('user.planid'));
			
			//loop for posted values and append to querystring
			foreach($arrData as $key => $value){
				$value = urlencode(stripslashes($value));
				$querystring = $querystring."$key=$value&";
			}
			
			// Append paypal return addresses
			$querystring = $querystring."return=".urlencode(stripslashes($return_url))."&";
			$querystring = $querystring."cancel_return=".urlencode(stripslashes($cancel_url))."&";
			$querystring = $querystring."notify_url=".urlencode($notify_url);
			$querystring = $querystring."_csrf=".urlencode('MXgxX2R5TDVIG24YEkE7YWETfgowOjt0RRRLbiIXOFgINl0MXSwkfw==');
			
			
			// Append querystring with custom field
			//$querystring .= "&custom=".USERID;
			header('location:http://www.sandbox.paypal.com/cgi-bin/webscr'.$querystring);
			exit;
			// Redirect to paypal IPN
			//$this->redirect('http://www.sandbox.paypal.com/cgi-bin/webscr'.$querystring);
		  }
			//return $this->goBack(Yii::$app->getModule("user")->loginRedirect);
            // set flash (which will show on the current page)
           
        }

        // render
        return $this->render('register', [
            'model' => $model,
        ]);
    }
	
	  /**
     * Forgot password
     */
    public function actionRconfirm()
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\forms\ForgotForm $model */

        // load post data and send email
        $model = Yii::$app->getModule("user")->model("ForgotForm");
     
        // render
        return $this->render("rconfirm", [
            "model" => $model,
        ]);
    }
	
	 
	
	  /**
     * Forgot password
     */
    public function actionPconfirm()
    {
       Yii::$app->session->set('user.pconfirm', 'YES');
	    $model = Yii::$app->getModule("user")->model("RegisterForm");
		
        if ($model->register()) {
       
            Yii::$app->session->setFlash(
                "Register-success",
                Yii::t("app", "The registration has been successfull. The password has been sent your registered email address. ")
            );
			  $this->redirect('rconfirm');
		  }else{
			 Yii::$app->session->setFlash(
                "Register-success",
                Yii::t("app", "The registration has not been successfull.")
            );
			  $this->redirect('rconfirm');
		  }
    }


    /**
     * Forgot password
     */
    public function actionForgot()
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\forms\ForgotForm $model */

        // load post data and send email
        $model = Yii::$app->getModule("user")->model("ForgotForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendForgotEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash(
                "Forgot-success",
                Yii::t("app", "Instructions to reset your password have been sent to your e-mail address.")
            );
        }

        // render
        return $this->render("forgot", [
            "model" => $model,
        ]);
    }

   
    /**
     * Resend email confirmation
     */
    public function actionResend()
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\forms\ResendForm $model */

        // load post data and send email
        $model = Yii::$app->getModule("user")->model("ResendForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("success", Yii::t("app", "Confirmation email resent"));
        }

        // render
        return $this->render("resend", [
            "model" => $model,
        ]);
    }

    /**
     * Confirm email
     *
     * @param $key
     * @return string
     */
    public function actionConfirm($key)
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\UserKey $userKey */
        /** @var \app\modules\user\models\User $user */

        // search for userKey
        $success = false;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByKey($key, [$userKey::TYPE_EMAIL_ACTIVATE, $userKey::TYPE_EMAIL_CHANGE]);
        if ($userKey) {

            // confirm user
            $user = Yii::$app->getModule("user")->model("User");
            $user = $user::findOne($userKey->user_id);
            $user->confirm();

            // consume userKey and set success
            $userKey->consume();
            $success = $user->email;
        }

        // render
        return $this->render("confirm", [
            "userKey" => $userKey,
            "success" => $success
        ]);
    }

    /**
     * Reset password
     *
     * @param $key
     * @return string
     */
    public function actionReset($key)
    {
        $this->layout = 'public';

        /** @var \app\modules\user\models\User    $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        // check for valid userKey
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByKey($key, $userKey::TYPE_PASSWORD_RESET);
        if (!$userKey) {
            return $this->render('reset', ["invalidKey" => true]);
        }

        // get user and set "reset" scenario
        $success = false;
        $user = Yii::$app->getModule("user")->model("User");
        $user = $user::findOne($userKey->user_id);
        $user->setScenario("reset");

        // load post data and reset user password
        if ($user->load(Yii::$app->request->post()) && $user->save()) {

            // consume userKey and set success = true
            $userKey->consume();
            $success = true;
        }

        // render
        return $this->render('reset', compact("user", "success"));
    }

    /**
     * Log user out and redirect
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        // redirect
        $logoutRedirect = Yii::$app->getModule("user")->logoutRedirect;
        if ($logoutRedirect === null) {
            return $this->goHome();
        } else {
            return $this->redirect($logoutRedirect);
        }
    }

    /**
     * Account
     */
    public function actionAccount()
    {
        /** @var \app\models\User $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        $this->layout = 'admin'; // In @app/views/user/views/layouts

        // set up user and load post data
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        if ($loadedPost && $user->validate()) {

            // generate userKey and send email if user changed his email
            if (Yii::$app->getModule("user")->emailChangeConfirmation && $user->checkAndPrepEmailChange()) {

                $userKey = Yii::$app->getModule("user")->model("UserKey");
                $userKey = $userKey::generate($user->id, $userKey::TYPE_EMAIL_CHANGE);
                if (!$numSent = $user->sendEmailConfirmation($userKey)) {

                    // handle email error
                    //Yii::$app->session->setFlash("Email-error", "Failed to send email");
                }
            }

            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("success", Yii::t("app", "Your account information has been updated."));
            return $this->refresh();
        }

        // render
        return $this->render("account", [
            'user' => $user,
        ]);
    }

    /**
     * Profile
     */
    public function actionProfile()
    {
        /** @var \app\models\Profile $profile */

        $this->layout = 'admin'; // In @app/views/user/views/layouts

        // set up profile and load post data
        $profile = Yii::$app->user->identity->profile;
        $loadedPost = $profile->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($profile);
        }

        // validate for normal request
        if ($loadedPost && $profile->validate()) {

            // Old image
            $oldImage = $profile->getImageFile();

            // Process uploaded image file instance
            /** @var UploadedFile $image */
            $image = $profile->uploadImage();

            if ($profile->save()) {
                // Upload only if valid uploaded image instance found
                if ($image !== false) {
                    // Delete old image and overwrite
                    @unlink($oldImage);
                    $path = $profile->getImageFile();
                    $image->saveAs($path);
                }
                Yii::$app->session->setFlash("success", Yii::t("app", "Your profile has been updated"));
                return $this->refresh();
            }
        }

        // render
        return $this->render("profile", [
            'profile' => $profile,
        ]);
    }

    /**
     * Change Username
     *
     * @return bool
     */
    public function actionChangeUsername()
    {
        /** @var \app\models\User $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        $this->layout = 'admin'; // In @app/views/user/views/layouts

        // set up user and load post data
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        if ($loadedPost && $user->validate()) {
            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("success", Yii::t("app", "Your username has been updated"));
            return $this->refresh();
        }

        // render
        return $this->render("username", [
            'user' => $user,
        ]);

    }


    /**
     * Change Email Address
     */
    public function actionChangeEmail()
    {
        /** @var \app\models\User $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        $this->layout = 'admin'; // In @app/views/user/views/layouts

        // set up user and load post data
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        if ($loadedPost && $user->validate()) {

            // generate userKey and send email if user changed his email
            if (Yii::$app->getModule("user")->emailChangeConfirmation && $user->checkAndPrepEmailChange()) {

                $userKey = Yii::$app->getModule("user")->model("UserKey");
                $userKey = $userKey::generate($user->id, $userKey::TYPE_EMAIL_CHANGE);
                if (!$numSent = $user->sendEmailConfirmation($userKey)) {

                    // handle email error
                    //Yii::$app->session->setFlash("Email-error", "Failed to send email");
                }
            }

            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("success", Yii::t("app", "Your email has been updated"));
            return $this->refresh();
        }

        // render
        return $this->render("email", [
            'user' => $user,
        ]);
    }

    /**
     * Change Password
     */
    public function actionChangePassword()
    {
        /** @var \app\models\User $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        $this->layout = 'admin'; // In @app/views/user/views/layouts

        // set up user and load post data
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        if ($loadedPost && $user->validate()) {
            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("success", Yii::t("app", "Your password has been updated"));
            return $this->refresh();
        }

        // render
        return $this->render("password", [
            'user' => $user,
        ]);
    }

    public function actionAvatarDelete()
    {

        // Delete for ajax request
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            // Set up profile and delete its avatar
            /** @var \app\models\Profile $profile */
            $profile = Yii::$app->user->identity->profile;
            if (!$profile->deleteImage()) {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t("app", "Has occurred an error deleting your profile image.")
                );
                return false;
            }
            $profile->save(false);
            Yii::$app->session->setFlash("success", Yii::t("app", "Your profile image has been deleted."));
            return true;
        }

        return '';
    }

    /**
     * Resend email change confirmation
     */
    public function actionResendChange()
    {
        /** @var \app\modules\user\models\User    $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        // find userKey of type email change
        $user    = Yii::$app->user->identity;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByUser($user->id, $userKey::TYPE_EMAIL_CHANGE);
        if ($userKey) {

            // send email and set flash message
            $user->sendEmailConfirmation($userKey);
            Yii::$app->session->setFlash("success", Yii::t("app", "Confirmation email resent"));
        }

        // redirect to account page
        return $this->redirect(["/user/account"]);
    }

    /**
     * Cancel email change
     */
    public function actionCancel()
    {
        /** @var \app\modules\user\models\User    $user */
        /** @var \app\modules\user\models\UserKey $userKey */

        // find userKey of type email change
        $user    = Yii::$app->user->identity;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByUser($user->id, $userKey::TYPE_EMAIL_CHANGE);
        if ($userKey) {

            // remove `user.new_email`
            $user->new_email = null;
            $user->save(false);

            // expire userKey and set flash message
            $userKey->expire();
            Yii::$app->session->setFlash("success", Yii::t("app", "Email change cancelled"));
        }

        // go to account page
        return $this->redirect(["/user/account"]);
    }
}
