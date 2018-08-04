<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.pages.controllers.LayoutControllerTrait');
Yii::import('common.modules.shops.controllers.ShopPageControllerTrait');
Yii::import('common.modules.shops.controllers.ShopParentController');
Yii::import('common.modules.shops.components.*');
/**
 * Description of LayoutController
 * This layout controller is used for Shop pages
 * 
 * @author kwlok
 */
class LayoutController extends ShopParentController
{
    use LayoutControllerTrait, ShopPageControllerTrait;
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        $this->modelType = 'Page';
        //-----------------
        // @see ImageControllerTrait
        $this->imageStateVariable = SActiveSession::SHOP_PAGEIMAGE;
        $this->setSessionActionsExclude();
        //-----------------//
        // Exclude following actions from rights filter 
        // @see ImageControllerTrait
        $this->rightsFilterActionsExclude = $this->getRightsFilterImageActionsExclude([
            $this->imageUploadAction,
            $this->multiImageUploadAction,
            $this->multiMediaGalleryFormGetAction,
            $this->multiMediaGallerySelectAction,
            'ckeditorimageupload',
        ]);
        //-----------------
        // ShopParentController Configuration
        //-----------------
        $this->showBreadcrumbsModule = true;
        $this->breadcrumbsModuleName = Page::model()->displayName(Helper::PLURAL);        
        $this->showBreadcrumbsController = false;
        $this->loadSessionParentShop();
        //-----------------
        //set shop assets path alias
        $this->setShopAssetsPathAlias();
    }
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),$this->storefrontBehaviors());
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),$this->layoutActions());
    }
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * @see ImageControllerTrait::runBeforeAction()
     */
    protected function beforeAction($action)
    {
        return $this->runBeforeAction($action);
    } 
    /**
     * @inheritdoc
     */
    public function getDefaultPage()
    {
        return ShopPage::defaultPage();
    }      
    /**
     * @inheritdoc
     */
    public function getPageOwner()
    {
        return $this->getParentShop();
    }
    /**
     * @inheritdoc
     */
    public function createPage($page, $model)
    {
        if ($model instanceof Product){
            return $this->createViewPage(ShopPage::PRODUCT,$model,'ProductPage');
        }
        elseif ($model instanceof CampaignBga){
            return $this->createViewPage(ShopPage::CAMPAIGN,$model,'CampaignPage');
        }
        elseif ($model instanceof Page){
            return $this->createViewPage(ShopPage::CUSTOM,$model,'CustomPage');
        }
        else {
            return $this->createViewPage($page,$model,'ShopPage');
        }
    }    
    /**
     * Get all the page owner themes
     * @param type $page
     * @return type
     */
    public function getPageOwnerThemes($page)
    {
        return ShopTheme::model()->locateShop($page->shopModel->id)->findAll();
    }

    public function getPageMenu($model, $page, $params=[]) 
    {
        $menu = parent::getPageMenu($model, $page, $params);
        return array_merge($menu,[
            ['id'=>'preview','title'=>Sii::t('sii','Preview Page'),'subscript'=>Sii::t('sii','preview'),
                'linkOptions'=>[
                    'target'=>'_blank',
                    'submit'=>$params['layoutPreviewUrl'],
                ]
            ],
            ['id'=>'layout','title'=>Sii::t('sii','Edit Content and Layout'),'subscript'=>Sii::t('sii','layout'), 'url'=>$params['layoutEditUrl'],'linkOptions'=>['class'=>'active']],
        ]);
    }   
    
}
