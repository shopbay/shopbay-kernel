<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WelcomeControllerBehavior
 *
 * @author kwlok
 */
abstract class WelcomeControllerBehavior extends CBehavior 
{
    /**
     * Define how welcome controller will be initialized
     */
    abstract public function initBehavior();
    /**
     * To define how scope filters were to be loaded
     * @see SPageIndexController
     * @return array
     */
    abstract public function loadScopeFilters();
    /**
     * To define the widget view based on scope filters since we set $customWidgetView to true in init()
     * @see WelcomeController::init()
     * @see SPageIndexController
     * @return array
     */
    abstract public function loadWidgetView($view,$scope,$searchModel=null);
    /**
     * Retrieve user recent activities
     * @return \CActiveDataProvider
     */
    public function getRecentActivity()
    {
        return new CActiveDataProvider(
            Activity::model()->mine()->operational()->recently(), [
            'criteria'=>[],
            'pagination'=> ['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);        
    }    
    /**
     * Retrieve user recent message
     * @return \CActiveDataProvider
     */
    public function getRecentMessages()
    {
        $finder = Message::model()->mine()->recently();
        return new CActiveDataProvider($finder, [
            'criteria'=>[],
            'pagination'=> ['pageSize'=>Config::getSystemSetting('news_per_page')],//reuse news per page
        ]);        
    }    
    /**
     * Show side bar of Welcome page
     * @return boolean Default to true
     */
    public function showSidebar()
    {
        return true;
    }          
    /**
     * Show recent activities at side bar of Welcome page
     * @return boolean Default to true
     */
    public function showRecentActivities()
    {
        return true;
    }          
    /**
     * Show recent messages at side bar of Welcome page
     * @return boolean Default to true
     */
    public function showRecentMessages()
    {
        return true;
    }  
    /**
     * Show ask question 'action button' under task view
     * @return boolean Default to false
     */
    public function showAskQuestion()
    {
        return false;
    }          
    /**
     * Show recent news at side bar of Welcome page
     * @return boolean Default to false
     */
    public function showRecentNews()
    {
        return false;
    }       
    /**
     * Show any additional advice when account is activated and first reach welcome page
     * @return string Default to null
     */
    public function renderAdvices()
    {
        return '';//default to nothing
    }           
}
