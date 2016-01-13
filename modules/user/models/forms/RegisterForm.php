<?php

namespace app\modules\user\models\forms;

use Yii;
use yii\web\Application;
use yii\web\ServerErrorHttpException;
use yii\base\Model;
use app\modules\user\models\User;
use app\modules\user\models\Role;
use app\modules\setup\models\Account;
use app\modules\setup\models\Profile;

/**
 * LoginForm is the model behind the login form.
 */
class RegisterForm extends Model
{
    /**
     * @var string Username and/or email
     */
    public $username;
	
	  /**
     * @var string Username and/or email
     */
    public $email;
	public $roleid;
	public $paytype;

   
  

    /**
     * @var \app\modules\user\models\User
     */
    protected $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [["username", "email","roleid"], "required"],
			["email", "email"],
            ["username", "validateUser"],
            ["email", "validateEmail"],
			
        ];
    }

    /**
     * Validate user
     */
    public function validateUser()
    {
        // check for valid user
        $this->_user = $this->getUser(1);
        if ($this->_user) {
            $this->addError("username", Yii::t("app", "Username already exist"));
        }
    }
	
	


 /**
     * Get user based on email and/or username
     *
     * @return \app\modules\user\models\User|null
     */
    public function getUser($flag)
    {
        // check if we need to get user
        if ($flag == 1) {

            // build query based on email and/or username login properties
            $user = Yii::$app->getModule("user")->model("User");
            $user = $user::find();
			$user->Where(["username" => $this->username]);
			 $this->_user = $user->one();
		}else{
			 $user = Yii::$app->getModule("user")->model("User");
            $user = $user::find();
			$user->Where(["email" => $this->email]);
			 $this->_user = $user->one();
		}

            // get and store user
           
       
        // return stored user
        return $this->_user;
    }
   
	
	 /**
     * Validate email exists and set user property
     */
    public function validateEmail()
    {
        // check for valid user
        $this->_user = $this->getUser(2);
        if ($this->_user) {
            $this->addError("email", Yii::t("app", "Email Already Exist"));
        }
    }


   

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        // calculate attribute label for "username"
        if (Yii::$app->getModule("user")->loginEmail && Yii::$app->getModule("user")->loginUsername) {
            $attribute = "Email / Username";
        } else {
            $attribute = Yii::$app->getModule("user")->loginEmail ? "Email" : "Username";
        }

        return [
            "username" => Yii::t("user", "User Name"),
            "email" => Yii::t("user", "Email"),
			"roleid" => Yii::t("user", "Membership Plan"),
			"paytype" => Yii::t("user", "Payment Type"),
            
        ];
    }
	
	 public function register()
    {
		
		$pconfirm = Yii::$app->session->get('user.pconfirm');
        if ($this->validate() && $pconfirm == '') {
		   $arrData = Yii::$app->request->post();
		   if($arrData['RegisterForm']['roleid'] == 2){
			   $oldApp = Yii::$app;
	
				$webConfigFile = Yii::getAlias('@app/config/web.php');
	
				if (!file_exists($webConfigFile) || !is_array(($webConfig = require($webConfigFile)))) {
					throw new ServerErrorHttpException('Cannot find `'.Yii::getAlias('@app/config/console.php').'`. Please create and configure console config.');
				}
	
				Yii::$app = new Application($webConfig);
	
				$transaction = Account::getDb()->beginTransaction();
				try {
					$account = new Account();
					$account->role_id = $arrData['RegisterForm']['roleid'];
					$account->status = 1;
					$account->email = $arrData['RegisterForm']['email'];
					$account->username = $arrData['RegisterForm']['username'];
					$account->password = Yii::$app->security->generatePasswordHash('test123');
					$account->auth_key = Yii::$app->security->generateRandomString();
					$account->api_key = Yii::$app->security->generateRandomString();
					$account->create_ip = Yii::$app->request->getUserIP();
					$account->create_time = date('Y-m-d H:i:s');
					$account->save();
					
					$profile = new Profile();
					$profile->user_id = $account->id;
					$profile->timezone = !empty($this->timezone) ? $this->timezone : null;
					$profile->language = $oldApp->language;
					$profile->create_time = date('Y-m-d H:i:s');
					$profile->save();
	
					$transaction->commit();
				} catch (\Exception $e) {
					// Rolls back the transaction
					$transaction->rollBack();
					return false;
				}
	
				Yii::$app = $oldApp;
				
			}else{
				Yii::$app->session->set('user.planid',$arrData['RegisterForm']['roleid']);
				Yii::$app->session->set('user.uname',$arrData['RegisterForm']['username']);
				Yii::$app->session->set('user.uemail',$arrData['RegisterForm']['email']);
			}
			return true;
        }else{//after payment done
			Yii::$app->session->set('user.pconfirm','');
			 $oldApp = Yii::$app;
	
				$webConfigFile = Yii::getAlias('@app/config/web.php');
	
				if (!file_exists($webConfigFile) || !is_array(($webConfig = require($webConfigFile)))) {
					throw new ServerErrorHttpException('Cannot find `'.Yii::getAlias('@app/config/console.php').'`. Please create and configure console config.');
				}
	
				Yii::$app = new Application($webConfig);
	
				$transaction = Account::getDb()->beginTransaction();
				try {
					$account = new Account();
					$account->role_id = Yii::$app->session->get('user.planid');
					$account->status = 1;
					$account->email = Yii::$app->session->get('user.uemail');
					$account->username = Yii::$app->session->get('user.uname');
					$account->password = Yii::$app->security->generatePasswordHash('test123');
					$account->auth_key = Yii::$app->security->generateRandomString();
					$account->api_key = Yii::$app->security->generateRandomString();
					$account->create_ip = Yii::$app->request->getUserIP();
					$account->create_time = date('Y-m-d H:i:s');
					$account->save();
					
					$profile = new Profile();
					$profile->user_id = $account->id;
					$profile->timezone = !empty($this->timezone) ? $this->timezone : null;
					$profile->language = $oldApp->language;
					$profile->create_time = date('Y-m-d H:i:s');
					$profile->save();
	
					$transaction->commit();
				} catch (\Exception $e) {
					// Rolls back the transaction
					$transaction->rollBack();
					return false;
				}
	
				Yii::$app = $oldApp;
				return true;
			
		}

        return false;
    }
}
