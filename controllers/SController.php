<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.rights.components.RController");
Yii::import("common.components.filters.ControllerFilterTrait");
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 * @author kwlok
 */
class SController extends RController
{
    use ControllerFilterTrait;
    /**
     * @var string the default layout for the SController view. 
     */
    public $layout;
    /**
     * @var string the default header view of $layout. 
     */
    public $headerView;
    /**
     * @var string the default footer view of $layout. 
     */
    public $footerView;
    /**
     * @var string the default html body css class. Defaults to 'Yii::app()->id'
     */
    public $htmlBodyCssClass;
    /**
     * @var string the default html body begin content before rendering header/content/footer
     */
    public $htmlBodyBegin;
    /**
     * @var string the default html body end content after rendering header/content/footer
     */
    public $htmlBodyEnd;
    /**
     * @var string the html header meta description tag
     */
    public $metaDescription;
    /**
     * @var string the html header meta keywords tag
     */
    public $metaKeywords;
    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu=[];
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs=[];
    /**
     * @var boolean if to have pageTitle suffix; Default to true
     */
    public $enablePageTitleSuffix=true;
    /**
     * @var string Page title suffix; Default to "app name"
     */
    public $pageTitleSuffix;
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        $this->layout = Yii::app()->ctrlManager->layout;
        $this->headerView = Yii::app()->ctrlManager->headerView;
        $this->footerView = Yii::app()->ctrlManager->footerView;
        $this->htmlBodyBegin = Yii::app()->ctrlManager->htmlBodyBegin;
        $this->htmlBodyEnd = Yii::app()->ctrlManager->htmlBodyEnd;
        $this->htmlBodyCssClass = Yii::app()->ctrlManager->htmlBodyCssClass;
        $this->pageTitleSuffix = Yii::app()->name;        
        $this->registerCssFile('application.assets.css','application.css');
        if ($this->allowOAuth){
            $this->registerZocial();
        }
        $this->setSiteLocale();
        if ($this->getModule()!=null)
            Yii::app()->messages->setModule($this->module->id);    
    }  
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'application',
                'pathAlias'=>'application.assets',
            ],
        ];
    }
    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @param string $type the model class to be loaded
     * @return mixed the loaded model
     * @throws CHttpException
     */
    public function loadModel($id,$type=null,$throws=true)
    {
        if ($type==null)
            $type = $this->modelType;
        else
            $type = ucfirst($type);
        $model = $type::model()->findByPk($id);
        if($model===null && $throws){
            logError(__METHOD__.' '.$type.' not found');
            throwError404(Sii::t('sii','The requested page does not exist'));
        }
        return $model;
    }    
    /*
     * Wrapper of Controller->renderPartial() and SModule->getView()
     */
    public function renderView($viewname, $data=NULL, $return=false)
    {
        if ($return){
            return $this->renderPartial($this->module->getView($viewname),$data,true);
        }
        else
            $this->renderPartial($this->module->getView($viewname),$data);
    }      
    /*
     * Wrapper of SModule->getImageURL() 
     */
    public function getImage($filename,$local=true)
    {
        if ($this->module===null)
            return $this->getImageURL($filename);
            
        if ($local)
            return $this->module->getImageURL($filename);//behavior of AssetLoaderBehavior
        else 
            return $this->module->getImage($filename);//method in SModule, to load external images from other modules
    }     
    /**
     * Load SModal Widget
     * @param type $container
     * @param type $content
     * @param type $cssStyle
     */
    public function smodalWidget($container=null,$content=null,$cssStyle=null,$closeScript=null,$return=false)
    {
        if ($return){
            return $this->widget('common.widgets.smodal.SModal',[
                'container'=>isset($container)?$container:'page_modal',
                'content'=>$content,
                'cssStyle'=>$cssStyle,
                'closeScript'=>$closeScript,
            ],true);            
        }
        else {
            $this->widget('common.widgets.smodal.SModal',[
                'container'=>isset($container)?$container:'page_modal',
                'content'=>$content,
                'cssStyle'=>$cssStyle,
                'closeScript'=>$closeScript,
            ]);
        }
    }
    /**
     * Load SFlash Widget
     * @param type $content
     */
    public function sflashWidget($key=null,$return=false)
    {
        if ($key==null)
            $key = $this->action->id;
        if ($return)
            return $this->widget('common.widgets.sflash.SFlash',['key'=>$key],true);
        $this->widget('common.widgets.sflash.SFlash',['key'=>$key]);
    }
    /**
     * Load SFlash Widget as a string
     * @param type $content
     */
    public function getFlashAsString($type,$message,$title)
    {
        user()->setFlash(__FUNCTION__,[
            'message'=>$message,
            'type'=>$type,
            'title'=>$title,
        ]);
        return $this->sflashWidget(__FUNCTION__,true);        
    }    
    /**
     * Load SImageZoomer Widget
     * @see common.widgets.simagezoomer.SImageZoomer
     * @param type $config
     */
    public function simagezoomerWidget($config=null,$return=false)
    {
        if ($return)
            return $this->widget('common.widgets.simagezoomer.SImageZoomer',$config,true);
        $this->widget('common.widgets.simagezoomer.SImageZoomer',$config);
    }    
    /**
     * Load SImageViewer Widget
     * @see common.widgets.simageviewer.SImageViewer
     * @param type $config
     */
    public function simageviewerWidget($config=null,$return=false)
    {
        if ($return)
            return $this->widget('common.widgets.simageviewer.SImageViewer',$config,true);
        $this->widget('common.widgets.simageviewer.SImageViewer',$config);
    }
    /**
     * Load SToolTip Widget
     * @see common.widgets.stooltip.SToolTip
     * @param type $content
     * @param type $config
     */
    public function stooltipWidget($content,$config=[],$return=false)
    {
        if ($return)
            return $this->widget('common.widgets.stooltip.SToolTip',['content'=>$content,'config'=>$config],true);
        $this->widget('common.widgets.stooltip.SToolTip',['content'=>$content,'config'=>$config]);
    }
    /**
     * Load search widget
     * @param type $placeholder
     * @param type $return
     * @return type
     */
    public function searchWidget($placeholder,$onsearch=null,$input=null,$return=true) 
    {
        if ($return)
            return $this->renderPartial('common.modules.search.views.default._searchbar',['placeholder'=>$placeholder,'onsearch'=>$onsearch,'input'=>$input],true);
        else
            $this->renderPartial('common.modules.search.views.default._searchbar',['placeholder'=>$placeholder,'onsearch'=>$onsearch,'input'=>$input]);
    }
    /**
     * set site locale from user locale
     * Called in init method
     */
    public function setSiteLocale()
    {
        Yii::app()->sourceLanguage = 'en';
        Yii::app()->language = user()->getLocale();
    }
    /**
     * Set user locale and store in session
     * @param type $locale
     */
    public function setUserLocale($locale)
    {
        user()->setLocale($locale);
        logInfo('Change locale to '.user()->getLocale());
    }
    /**
     * @return boolean if oauth is supported
     */
    public function getAllowOAuth()
    {
        return param('OAUTH')!=null && param('OAUTH');
    }
    /**
     * Return the correct authentication host info that supports authentication
     * @return type
     */
    public function getAuthHostInfo()
    {
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],Yii::app()->urlManager->merchantDomain) != false) {
            return Yii::app()->urlManager->createMerchantUrl('',true);
        }
        else
            return Yii::app()->urlManager->createHostUrl('',true);
    }
    /**
     * Check if the request is on the same domain as http referer
     * @param type $hostInfo
     * @return type
     */
    public function isSameOrigin($hostInfo)
    {
        return isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$hostInfo) != false;
    } 
    /**
     * Override page title setting to concatenate app name as suffix
     * @param string $title
     */
    public function setPageTitle($title) 
    {
        if ($this->enablePageTitleSuffix && !empty($this->pageTitleSuffix))
            $title = $title.' | '.$this->pageTitleSuffix;

        parent::setPageTitle($title);
    }   
    
    public function setHeaderHomeUrl($url)
    {
        Yii::app()->urlManager->homeUrl = $url;
    }
}