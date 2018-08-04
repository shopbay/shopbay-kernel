<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.spageindex.SPageIndex");
Yii::import("common.widgets.spageindex.actions.SPageIndexAction");
Yii::import('common.modules.tasks.components.TaskBaseController');
/**
 * Description of WelcomeController
 * This controller extends directly from AuthenticatedController, and bypass AccountBaseController
 * 
 * @author kwlok
 */
class WelcomeController extends SPageIndexController 
{
    private $_presignupForm;
    public $modelView;
    public $tasksView;
    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->viewName = Sii::t('sii','Welcome!');
        $this->route = 'account/welcome/index';
        $this->pageControl = SPageIndex::CONTROL_ARROW;
        $this->enableViewOptions = false;
        $this->enableSearch = false;
        $this->customWidgetView = true;
        $this->index = $this->module->welcomeView;
        //-----------------//
        if ($this->hasWelcomeBehavior)
            $this->initBehavior();
    }    
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        if ($this->hasWelcomeBehavior){
            return array_merge(parent::behaviors(),[
                'welcomebehavior' => [
                    'class'=>$this->module->welcomeControllerBehavior,
                ],
            ]);
        }
        else
            throw new CException('WelcomeControllerBehavior is not set.');
    }         
    /**
     * @return boolean if welcome behavior is enabled
     */
    public function getHasWelcomeBehavior()
    {
        return isset($this->module->welcomeControllerBehavior);
    } 
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        if ($this->hasWelcomeBehavior)
            return $this->loadScopeFilters();
        else
            throwError404(Sii::t('sii','App not found'));
    }
    /**
     * OVERRIDE METHOD, since we set $customWidgetView to true in init()
     * 
     * @see SPageIndexController
     * @return string view file name
     */
    public function getWidgetView($view,$scope=null,$searchModel=null)
    {
        if ($this->hasWelcomeBehavior)
            return $this->loadWidgetView($view,$scope,$searchModel);
        else 
            throwError404(Sii::t('sii','The requested page does not exist'));
    }    
    /**
     * Below action is called when js:quickdashboard() is invoked - when user is switching 'Arrow Tabs' at index page
     */
    public function actionDashboard()
    {
        header('Content-type: application/json');
        echo CJSON::encode($this->getChartWidgetData());
        Yii::app()->end();
    }
    /**
     * Showing the flashes message including Wizard helps
     * @return type
     */
    protected function getFlashes()
    {
        if ($this->defaultScope=='activate'){//@see loadPreSignupForm
            $flash = array('welcome-message','activate-guide');//flash id for "activate" scope
        }
        else {
            $flash = $this->loadWizards($this->id,user());
        }
        return $flash;
    }
    /**
     * For scenario when user is registered only but not yet activated 
     * Normally users are coming from social network
     */
    public function loadPreSignupForm()
    {
        if (!isset($this->_presignupForm)){
            $this->_presignupForm = PreSignupForm::createForm(user());
        }
        return $this->_presignupForm;
    }
    /**
     * Prepare the pre-signup messages as flashes to show to users
     * @see ActivateController::preparePresignupMessages()
     */
    public function loadPreSignupMessages()
    {
        $this->defaultScope = 'activate';
        $form = $this->loadPreSignupForm();
        $this->module->runControllerMethod('accounts/activate','preparePresignupMessages',$form->network,$this->getFlashes());
    }
    /**
     * A direct page to activate presignup
     * This is used at merchant app when user first sign in using oauth and has no subscription yet
     * @see plans/controllers/SubscriptionController::actionIndex()
     */
    public function renderActivatePreSignup()
    {
        $this->loadPreSignupMessages();
        $this->render('activate_presignup');
    }
    /**
     * Password reset action
     * This is used for user to reset password upon first time login
     * Applicable to app that allows to create users; e.g. Admin
     * Apps with signup capability does not need this
     * 
     * @see common.components.filters.WelcomeFilter
     */
    public function actionPasswordReset()
    {
        $this->registerCommonFiles();
        $this->registerCssFile('admin.assets','application.css');
        $this->registerFontAwesome();
        $this->registerMaterialIcons();

        $form = new SimplePasswordForm(user()->isSuperuser?'superuser':'');
        if (isset($_POST['SimplePasswordForm'])){
            try {

                $form->attributes=$_POST['SimplePasswordForm'];

                $this->getOwner()->module->serviceManager->firstPasswordReset(user()->account,$form);

                user()->setFlash(get_class($form),array(
                    'message'=>Sii::t('sii','Password changed successfully.'),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Change Password')));

                unset($_POST);

                $success = true;

            } catch (CException $e) {
                user()->setFlash(get_class($form),array(
                    'message'=>$e->getMessage(),
                    'type'=>'error',
                    'title'=>Sii::t('sii','Change Password')));
            }
        }

        //always have to re-enter password for new submission
        $form->unsetAttributes(['newPassword','confirmPassword']);//keep email to be used inside the view
        $this->getOwner()->render('password_reset',['form'=>$form,'success'=>isset($success)?$success:false]);
    }
    /**
     * Supported method for action PasswordReset
     * Specifies the local access control rules.
     * @see SSiteController::accessRules()
     * @return array access control rules
     */
    public function accessRules()
    {
        return [
            ['allow',
                'actions'=>['passwordReset'],
                'users'=>['@'],
            ],
        ];
    }        
}
