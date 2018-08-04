<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AuthenticatedController
 *
 * @author kwlok
 */
class AuthenticatedController extends SController 
{
    /*
     * Action to be excluded from rights filter
     */
    public $rightsFilterActionsExclude = [];
    
    protected $modelType = 'undefined';
    protected $serviceNotAvailableAction = 'serviceNotAvailable';
    protected $serviceNotAvailableJsonAction = 'serviceNotAvailableJsonAction';
    /**
     * A global message push to the top of each page
     * @var type 
     */
    protected $globalFlash;
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        $this->layout = Yii::app()->ctrlManager->authenticatedLayout;
        $this->headerView = Yii::app()->ctrlManager->authenticatedHeaderView;
        $this->footerView = Yii::app()->ctrlManager->authenticatedFooterView;
        $this->globalFlash = new CList();
    }  
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'wizardbehavior' => [
                'class'=>'common.modules.help.wizards.behaviors.WizardControllerBehavior',
                'appId'=>param('WIZARD_APP_ID'),
            ],
        ]);
    }     
    /**
     * @return array action filters
     */
    public function filters()
    {
        $filters = [
            [Yii::app()->ctrlManager->pageTitleSuffixFilter,'useShopName'=>true],//when shop scope is true
        ];
        $filters = array_merge($filters,Yii::app()->filter->rules);
        foreach ($this->rightsFilterActionsExclude as $index => $action) {
            if ($index==0)//first one
                $rightsFilter = 'rights - '.$action.',';
            else
                $rightsFilter .= $action.',';
        };
        if (isset($rightsFilter))
            $filters = array_merge($filters,[rtrim($rightsFilter, ",")]);
        else
            $filters = array_merge($filters,['rights']);
        
        logTrace(__METHOD__,$filters);
        return $filters;
    }       

    public function sloaderWidget($type=SLoader::FIXED,$display='none',$text=null)
    {
        $this->widget('common.widgets.sloader.SLoader',['type'=>$type,'display'=>$display,'text'=>$text]);
    }
        
    public function spageindexWidget($config,$renderPartial=false)
    {
        if ($renderPartial){
            $config = array_merge(['renderPartial'=>true],$config);
            return $this->widget('common.widgets.spageindex.SPageIndex', $config);
        }
        else
            $this->widget('common.widgets.spageindex.SPageIndex', $config);
    }
    
    public function spagesectionWidget($dataProviders,$buttonMode=null,$return=false)
    {
        if ($return){
            return $this->widget('common.widgets.spagesection.SPageSection',['sections'=>$dataProviders,'buttonMode'=>$buttonMode],true);
        }
        else
            $this->widget('common.widgets.spagesection.SPageSection',['sections'=>$dataProviders,'buttonMode'=>$buttonMode]);
    }
    /*
     * Wrapper of AssetLoaderBehavior->getAssetsURL() 
     */
    public function getAssetsURL($pathAlias=null)
    {
       if ($this->module===null)
            return Yii::app()->getModule('accounts')->getAssetsURL($pathAlias);//use accounts module as default
       else
            return $this->module->getAssetsURL($pathAlias);//method in AssetLoaderBehavior
    }
    
    public function getModelDisplayName($obj_type,$plural=false)
    {
        $type =  SActiveRecord::resolveTablename($obj_type);
        return $type::model()->displayName($plural?Helper::PLURAL:Helper::SINGULAR);
    }    

    public function getLoginReturnUrl($type,$id)
    {
        $model = $type::model()->findbyPk($id);
        return $model->returnUrl;
    }
    
    public function addGlobalFlash($type,$title,$message=null)
    {
        $this->globalFlash->add([
            'type'=>$type,
            'title'=>$title,
            'message'=>$message,
        ]);
    }
    
    public function clearGlobalFlash()
    {
        $this->globalFlash->clear();
    }
    
    public function renderGlobalFlash()
    {
        if (!empty($this->globalFlash)){
            foreach ($this->globalFlash as $flash) {
                echo $this->getFlashAsString($flash['type'],$flash['message'],$flash['title']);     
            }
            $this->globalFlash->clear();
        }
        else
            echo null;
    }
    
    public function getProfileSidebar($menu=null,$width=SPageLayout::WIDTH_10PERCENT,$columnPosition=SPageLayout::COLUMN_LEFT)
    {
        return [
            $columnPosition => [
                'content'=>$this->renderView('accounts.profilesidebar',['menu'=>$menu],true),
                'cssClass'=>$width,
            ]
        ];
    }
    
}