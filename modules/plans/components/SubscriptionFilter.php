<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SubscriptionFilter
 *
 * @author kwlok
 */
class SubscriptionFilter extends CFilter
{
    protected $filterClass = 'SubscriptionFilter';
    protected $redirectUrl;
    protected $access = true;
    /**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        $this->redirectUrl = $this->getDefaultRedirectUrl();
        
        if (Role::requiresSubscriptionCheck(user()->currentRole)){
            
            //Bug fix: Function create_function() is deprecated in php 7.2.0
            //ErrorException: Function create_function() is deprecated (/.../yii-1.1.20.6ed384/framework/logging/CProfileLogRoute.php:182)\n#0 
            // Comment on beginProfile()
            //Yii::beginProfile(__METHOD__.' Check user level subscriptions');
            /**
             * @todo Below checks are also on user level and may can be checked during user login. and not here?
             * But here we implement as filter so if any app does not include this filter, check are not enforced.
             * So, to keep it at filter level, maybe here is better place.
             */
//            if (!Plan::hasFreeTrialInstance()){
//                $this->access = false;
//                user()->setFlash('Plan',[
//                    'message'=>Sii::t('sii','This system is not attached to any plans.'),
//                    'type'=>'error',
//                    'title'=>Sii::t('sii','Subscription Error.'),
//                ]);
//            }
            //Subscription checks are now at each shop level. 
            //@see ShopSubscriptionFilter
            //[1] Minimally make sure user must have at least one active subscription
//            elseif (empty(user()->onlineSubscriptions)){
//                $this->access = false;
//                $message = user()->hasTrialBefore ? null : Sii::t('sii','Try the {n}-days full featured free trial and see which paid plan is right for you.',['{n}'=>Plan::freeTrialInstance()->duration]);
//                $title = user()->hasExpiredFreeTrial ? Sii::t('sii','Your {plan} has expired.',['{plan}'=>Sii::t('sii','Free Trial')]) : Sii::t('sii','Please choose a plan.') ;
//                user()->setFlash('Plan',[
//                    'message'=>$message,
//                    'type'=>'notice',
//                    'title'=>$title,
//                ]);
//            }
//            elseif (!user()->hasNonFreeTrialSubscriptions && user()->hasExpiredFreeTrial){
//                $this->access = false;     
//                $message = Sii::t('sii','To continue using our service, you can choose any of our plan offerings.');
//                user()->setFlash('Plan',[
//                    'message'=>$message,
//                    'type'=>'notice',
//                    'title'=>Sii::t('sii','Your {plan} has expired.',['{plan}'=>Sii::t('sii','Free Trial')]),
//                ]);
//            }
            
           //Yii::endProfile(__METHOD__.' Check user level subscriptions');
        
           // Yii::beginProfile(__METHOD__.' Check module level subscription');

            $this->validateSubscriptionServices($filterChain);
        
           // Yii::endProfile(__METHOD__.' Check module level subscription');
        
        }
        
        return $this->verifyAccess($filterChain);
    }
    /**
     * Logic being applied after the action is executed
     * @param type $filterChain
     */
    protected function postFilter($filterChain)
    {
        //put logic here
    }
    /**
     * Validate subscription rules (if user has subscribed to a particular service)
     * @todo Suggest to load all subscription licenses at one go when login to make page loading faster 
     */
    protected function validateSubscriptionServices($filterChain)
    {
        $rules = $this->getServiceRules($filterChain);
        if (isset($rules)){
            if (isset($rules['subscriptionRequired']) && $rules['subscriptionRequired']){
                $this->access = false;
                user()->setFlash('Plan',[
                    'message'=>isset($rules['flashMessage'])?$rules['flashMessage']:null,
                    'type'=>'notice',
                    'title'=>isset($rules['flashTitle'])?$rules['flashTitle']:Sii::t('sii','Please choose a plan.'),
                ]);
                if (isset($rules['queryParams']))
                    $this->redirectUrl .= '?'.http_build_query($rules['queryParams']);
            }
            else {
                $this->checkSubscription($filterChain,$rules);  
            }
        }
    }
    /**
     * Check if a user has subcribed to service
     * @param type $filterChain
     * @param type $params
     */
    protected function checkSubscription($filterChain,$params=[])
    {
        Yii::import('common.components.actions.api.ApiCheckAction');
        $action = new ApiCheckAction($filterChain->controller,__METHOD__);
        foreach ($params as $key => $value) {
            if (in_array($key, ['permission','jsonResponseOnRejection','redirectUrlOnRejection','flashId','flashMessage','flashTitle'])){
                $action->{$key} = $value;
                logTrace(__METHOD__.' ApiCheckAction->'.$key.' = ',$value);
            }
        }
        
        $postFields = [];
        if (isset($params['shopFilter']) && $params['shopFilter']){
            if ($filterChain->controller->hasSessionShop())
                $postFields['shop'] = $filterChain->controller->getSessionShop();
        }
        //Add on other post fields if any
        if (isset($params['GET']) && is_array($params['GET'])){
            foreach ($params['GET'] as $key => $value) {
                $postFields[$key] = $value=='undefined'?0:$_GET[$value];//'0' means key not found, is a new object
            }
        }
        
        if (!Feature::hasPattern($action->permission))//exact feature name
            $action->permission = Feature::getKey($action->permission);
        
        $action->postFields = $postFields;
            
        $filterChain->controller->runAction($action);
    }    
    /**
     * Find the service rule according to controller route
     * @param type $filterChain
     * @return type
     */
    protected function getServiceRules($filterChain)
    {
        $rules = include Yii::getPathOfAlias('common.modules.plans.components').DIRECTORY_SEPARATOR.'SubscriptionRules.php';
        $ruleKey = $this->controllerRoute($filterChain);
        
        if (request()->getIsPostRequest()){
            $ruleKey = 'post:'.$ruleKey.'?service='.request()->getQuery('service');
            logInfo(__METHOD__.' post request! '.$ruleKey);
            if ($this->existsRule($rules, $ruleKey)){
                $params = $rules[$ruleKey];
                if (isset($params['postModel'])&&isset($params['postField'])&&isset($_POST[$params['postModel']][$params['postField']])){
                    logInfo(get_called_class().'::getCheckRules rule matched with POST params! '.$params['postModel'].'.'.$params['postField']);
                    return $params;
                }
            }
        }
        else {//GET request
            if ($this->existsRule($rules, $ruleKey)){
                logInfo(get_called_class().'::getCheckRules rule matched! '.$ruleKey,$rules[$ruleKey]);
                return $rules[$ruleKey];
            }            
        }
        logTrace(get_called_class().'::getCheckRules no rule found, skip '.$ruleKey);
        return null;
    }
    /**
     * Find if rule exists to proceed
     * @param type $rules
     * @param type $ruleKey
     * @return type
     */
    protected function existsRule($rules,$ruleKey)
    {
        return isset($rules[$ruleKey]) && isset($rules[$ruleKey]['checkBy']) && $rules[$ruleKey]['checkBy']==$this->filterClass;
    }
    /**
     * Find the controller route for rule matching
     * @param type $filterChain
     * @return string
     */
    protected function controllerRoute($filterChain)
    {
        $controllerRoute = $filterChain->controller->uniqueId.'/'.$filterChain->action->id;
        logTrace(get_called_class().'::controllerRoute',$controllerRoute);
        return $controllerRoute;
    }
    /**
     * Verify if to grant access
     * @param type $filterChain
     * @return boolean
     */
    protected function verifyAccess($filterChain)
    {
        if (!$this->access){
            //set return url 
            Yii::app()->user->returnUrl = url(Yii::app()->request->getRequestUri());
            $filterChain->controller->redirect($this->redirectUrl);
            Yii::app()->end();
        }
        else 
            return true;
    }
    /**
     * Denies the access of the user.
     * @throws CHttpException when called unless login is required.
    */
    protected function denyAccess()
    {
        if (user()->isGuest===true)
            user()->loginRequired();
        else 
            throw new CHttpException(403, Sii::t('sii','You have no subscription to perform this action.'));
    }      
    
    protected function getDefaultRedirectUrl($params=[])
    {
        return url('plans/subscription',$params);
    }
    
}
