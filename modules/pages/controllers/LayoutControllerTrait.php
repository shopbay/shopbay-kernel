<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.simagemanager.controllers.ImageControllerTrait');
Yii::import('common.modules.shops.components.BasePage');
Yii::import('common.modules.pages.models.PageViewInterface');
/**
 * Description of LayoutControllerTrait
 *
 * @author kwlok
 */
trait LayoutControllerTrait 
{
    use ImageControllerTrait;

    protected $multiImageStateVariable = SActiveSession::PAGE_MULTIIMAGE;         
    protected $multiImageUploadAction = 'multiimageupload';
    protected $multiMediaGalleryFormGetAction = 'multimediagalleryformget';
    protected $multiMediaGallerySelectAction = 'multimediagalleryselect';
    /**
     * The current accesing page underlying model (either Shop, Product, Campaign, Page etc)
     * @var CActiveRecord
     */
    protected $model;
    /**
     * The current editing page
     * @var ShopPage 
     */
    protected $page;
    /**
     * The current editing theme
     * @var ShopTheme 
     */
    protected $theme;
    /**
     * Declares class-based actions.
     */
    public function layoutActions()
    {
        return array_merge($this->imageActions(SImageManager::SINGLE_IMAGE,[],[$this->mediaGallerySelectAction]),[
            //separate set for multiple images upload (support slide show)
            $this->multiImageUploadAction => [
                'class'=>'common.widgets.simagemanager.actions.ImageUploadAction',
                'multipleImages'=>SImageManager::MULTIPLE_IMAGES,
                'formClass'=>'common.widgets.simagemanager.models.MultipleImagesForm',
                'stateVariable'=> $this->multiImageStateVariable,
                'secureFileNames'=>true,
                'uploadLimit'=>99,//set to big number to avoid limit hit
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ],
            $this->multiMediaGalleryFormGetAction => [
                'class'=>'common.modules.media.actions.MediaGalleryFormGetAction',
                'formModel'=>SImageManager::MULTIPLE_IMAGES,
                'mediaGallerySelectAction'=>$this->multiMediaGallerySelectAction,
            ],            
            $this->multiMediaGallerySelectAction => [
                'class'=>'common.modules.pages.actions.MultiMediaGallerySelectAction',
                'formModel'=>SImageManager::MULTIPLE_IMAGES,
                'stateVariable' => $this->multiImageStateVariable,
                'viewFile'=>'common.modules.pages.views.layout._multiimage',
            ],    
            $this->multiMediaGallerySelectAction => [
                'class'=>'common.modules.pages.actions.MultiMediaGallerySelectAction',
                'formModel'=>SImageManager::MULTIPLE_IMAGES,
                'stateVariable' => $this->multiImageStateVariable,
                'viewFile'=>'common.modules.pages.views.layout._multiimage',
            ],    
            //customize $this->mediaGallerySelectAction
            $this->mediaGallerySelectAction => [
                'class'=>'common.modules.media.actions.MediaGallerySelectAction',
                'formModel'=>SImageManager::SINGLE_IMAGE,
                'stateVariable' => $this->imageStateVariable,
                'viewFile'=>'common.modules.pages.views.layout._singleimage',
            ],    
            'ckeditorimageupload'=>[
                'class'=>'common.components.actions.CkeditorImageUploadAction',
            ],
        ]);
    }
    /**
     * Default page for index action
     */
    public function getDefaultPage()
    {
        return BasePage::HOME;
    }
    /**
     * Get page owner
     */
    public function getPageOwner()
    {
        throw new CException('Please define page owner');
    }
    /**
     * Get all the page owner themes
     * @param type $page
     * @return type
     */
    public function getPageOwnerThemes($page)
    {
        throw new CException('Please implement page owner theme selections');
    }    
    /**
     * Create page 
     * Default implementation; For class uses this trait to customize
     */
    public function createPage($page, $owner)
    {
        return $this->createViewPage($page,$owner);
    }
    /**
     * Index action
     * @see merchant/config/main.php for url mapping: <route>/pages/layout/edit/<page_name>
     */
    public function actionIndex()
    {
        $page = $this->getEditPage(isset($_GET['page_name'])?$_GET['page_name']:$this->getDefaultPage());
        //This is only accessed under page edit mode. So page model must exist!
        if ($page->pageModel==null)
            throwError404 (Sii::t('sii','Page not found'));
        
        $this->render('common.modules.pages.views.layout.index',[
            'page'=>$page,
            'pageModel'=>$this->model,
            'editor'=>$this->getEditor($page),
        ]);
    }
    
    public function actionUpdate()
    {
        if (isset($_POST['page']) && 
            isset($_POST['theme']) && 
            isset($_POST['name']) && //layout name
            isset($_POST['layout'])){ //layout config
    
            try {
                logTrace(__METHOD__.' Received POST layout', $_POST['layout']);
                $layout = json_decode($_POST['layout'],true);

                //Returning PageLayout model
                $model = $this->pageManager->saveLayout(user()->getId(),$this->getPageOwner(),$_POST['page'],$_POST['theme'],$_POST['name'],$layout);
                user()->setFlash($this->id,[
                    'message'=>Sii::t('sii','Layout for page "{page}" and theme "{theme}" is saved successfully.',['{page}'=>$_POST['page'],'{theme}'=>$_POST['theme']]),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Page Layout Update'),
                ]);
                unset($_POST);
                header('Content-type: application/json');
                echo CJSON::encode([
                    'status'=>'success',
                    'redirect'=>$this->appendPageQueryParams($model->viewUrl),//append back query params if any, to support theme layout editing switching
                ]);
                
            } catch (CException $ex) {
                logError(__METHOD__.' page layout update error',$ex->getTraceAsString());
                header('Content-type: application/json');
                echo CJSON::encode([
                    'status'=>'failure',
                    'message'=>$ex->getMessage(),
                ]);
            }
            
            Yii::app()->end();
        }
        else
            throwError400('Bad request');
    }
    /**
     * Action to retrieve category block html block (data provider portion)
     * @param type $id The category data provider id
     * @param type $itemsperrow Items per row
     * @param type $limit Items limit
     */
    public function actionCategory($id,$itemsperrow,$limit)
    {
        if (request()->getIsPostRequest()){
            //use dummy default page, getting widget is not using page inside
            $page = $this->getEditPage($this->getDefaultPage());
            $widget = $this->getEditor($page)->getWidget('SGridCategoryBlockWidget',[
                'category'=>$id=='0'?null:$id,
                'itemsPerRow'=>$itemsperrow,
                'itemsLimit'=>$limit,
                //viewData is pickup from the default from layout_map.json
            ]);
            header('Content-type: application/json');
            echo CJSON::encode([
                'category'=>$id,
                'items_per_row'=>$itemsperrow,
                'items_limit'=>$limit,
                'html'=>$widget->renderListView(),
            ]);
            Yii::app()->end();
        }
        else
            throwError400('Bad request');
    }
    /**
     * Action to retrieve list block html data (data provider portion)
     * @param type $item The list item 
     * @param type $itemsperrow Items per row
     * @param type $limit Items limit
     */
    public function actionListItem($item,$itemsperrow,$limit)
    {
        if (request()->getIsPostRequest()){
            //use dummy default page, getting widget is not using page inside
            $page = $this->getEditPage($this->getDefaultPage());
            $widget = $this->getEditor($page)->getWidget('SGridListBlockWidget',[
                'listItem'=>$item=='0'?null:$item,
                'itemsPerRow'=>$itemsperrow,
                'itemsLimit'=>$limit,
                //viewData is pickup from the default from layout_map.json
            ]);
            header('Content-type: application/json');
            echo CJSON::encode([
                'listItem'=>$item,
                'items_per_row'=>$itemsperrow,
                'items_limit'=>$limit,
                'html'=>$widget->renderListView(),
            ]);
            Yii::app()->end();
        }
        else
            throwError400('Bad request');
    }    
    /**
     * Action to reset page layout
     * @param string $page page id
     * @param string $theme theme id
     */
    public function actionReset($page,$theme)
    {
        if (request()->getIsPostRequest()){
            //Returning valid page layout url if no error
            $layoutUrl = $this->pageManager->resetLayout(user()->getId(),$this->getPageOwner(),$theme,$page);
            user()->setFlash($this->id,[
                'message'=>Sii::t('sii','Layout for page "{page}" and theme "{theme}" is reset successfully.',['{page}'=>$page,'{theme}'=>$theme]),
                'type'=>'success',
                'title'=>Sii::t('sii','Page Layout Reset'),
            ]);
            $this->redirect($layoutUrl);
        }
        throwError403('Unauthorized request');
    }        
    /**
     * Append back query params if any, to support theme layout editing switching when saved
     * @return string
     */
    protected function appendPageQueryParams($url)
    {
        $uri = Helper::parseUri();
        return $url.'?'.http_build_query($uri['query']);
    }    
    /**
     * Create page url 
     * Default implementation; For class uses this trait to customize
     */
    protected function getPageUrlRoute($action)
    {
        return url($this->getActionRoute($action));
    }
    /**
     * Create page action route 
     * Default implementation; For class uses this trait to customize
     */
    protected function getActionRoute($action)
    {
        return $this->module->id .'/'.$this->id.'/'.$action;
    }    
    /**
     * Append back query params if any, to support theme layout editing switching when saved
     * @return type
     */
    public function getUpdateUrl()
    {
        return $this->appendPageQueryParams($this->getPageUrlRoute('update'));
    }
    
    public function getUploadUrl($multiple=false)
    {
        $action = $multiple ? $this->multiImageUploadAction : $this->imageUploadAction;
        return $this->getPageUrlRoute($action);
    }

    public function getUrlFormGetUrl($multiple=false)
    {
        //to support multiple since not in use now
        return $this->getPageUrlRoute($this->imageUrlFormGetAction);
    }
    
    public function getMediaGalleryFormGetUrl($multiple=false)
    {
        $action = $multiple ? $this->multiMediaGalleryFormGetAction : $this->mediaGalleryFormGetAction;
        return $this->getPageUrlRoute($action);
    }    
    
    public function getImageForm($element,$multiple=false)
    {
        $this->widget('common.widgets.simagemanager.SImageManager', [
            'url'=>$this->getUploadUrl($multiple),
            'imageOwner'=>$element,
            'imageFormModel'=> $multiple ? SImageManager::MULTIPLE_IMAGES : SImageManager::SINGLE_IMAGE,
            'autoUpload'=>true,
            'showLabel'=>false,   
            'multiple'=>$multiple,
            'formView'=> $multiple ? 'common.modules.pages.views.layout._multiimage_form' : 'form',
            'singleFormView'=>'common.modules.pages.views.layout._singleimage_form',
            'imageView'=> $multiple ? '-na-' : 'common.modules.pages.views.layout._singleimage',//mulliple image for slide images view file is handled using javascript
            'mediaGalleryFormGetUrl'=>$this->getMediaGalleryFormGetUrl($multiple),        
            'mediaGalleryScript'=>$multiple ? 'mediagalleryform_multiimage' : 'mediagalleryform_singleimage',
            //'urlFormGetUrl'=>$this->getUrlFormGetUrl($multiple),//not support for now
        ]);         
    } 
    /**
     * The page that is to be edit
     * @param string $page The page id
     * @return ShopPage
     */
    protected function getEditPage($page)
    {
        if (!isset($this->page)){
            $this->model = PageLayout::findPageModel($this->getPageOwner(), $page);
            $this->page = $this->createPage($page, $this->model);
            $this->page->edit = true;//set to edit mode
        }
        return $this->page;
    }    
     
    protected function getEditor($page)
    {
        $editor = new PageLayoutEditor();
        $editor->page = $page;
        $editor->theme = $this->getLayoutTheme($page);
        $editor->locale = user()->getLocale();
        $editor->init();
        return $editor;
    }
    /**
     * @return the current layout theme model
     * @param PageViewInterface $page object
     */
    protected function getLayoutTheme(PageViewInterface $page)
    {
        if (!isset($this->theme)){
            $this->theme = $this->loadTheme($page->currentTheme,$page->currentStyle);        
        }
        return $this->theme;
    }
    /**
     * Create page manager
     * Default implementation; For class uses this trait to customize
     */
    protected function getPageManager()
    {
        return $this->module->serviceManager;
    }
  
}
