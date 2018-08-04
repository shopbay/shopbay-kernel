<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopViewPage");
/**
 * Description of ProductPage
 *
 * @author kwlok
 */
class ProductPage extends ShopViewPage 
{
    const MODAL      = 'product_modal_page';
    const SPEC       = 'product_spec_page';
    const QUESTION   = 'product_question_page';
    const COMMENT    = 'product_comment_page';
    /**
     * Get page data
     * @return type
     * @throws CException
     */
    public function getData($locale=null) 
    {
        logTrace(__METHOD__.' load data for '.$this->id);
        if ($this->id==self::MODAL){
            return [
                'view'=>$this->controller->getThemeView('_product_modalview'),
                'data'=>[
                    'model'=>$this->model,
                    'page'=>$this,
                    'cartForm'=>$this->getCartItemForm(),
                    'likeForm'=>$this->getLikeForm(true),
                    'commentDataProvider'=>$this->model->searchComments(),
                    'commentForm'=>$this->getCommentForm('comment-modal-form'),
                ],
            ];               
        }
        else {
            return [
                'view'=>$this->controller->getThemeView('_product_view'),
                'data'=>[
                    'model'=>$this->model,
                    'cartForm'=>$this->cartItemForm,
                    'likeForm'=>$this->likeForm,
                    'commentForm'=>$this->commentForm,
                    'pages'=>$this->getSubPagesData(),
                ],
            ];               
        }
    }
    
    public function getSubPagesData()
    {
        return [
            self::SPEC=>[
                'title'=>'<div class="title">'.Sii::t('sii','Specification').'</div>',
                'view'=>$this->controller->getThemeView('_product_spec'),
                'data'=>['idx'=>'spec','details'=>$this->model->displayLanguageValue('spec',user()->getLocale())],
            ],
            self::COMMENT=>[
                'title'=>'<div class="title">'.Sii::t('sii','Comments').'</div>',
                'view'=>$this->controller->getThemeView('_product_comments'),
                'data'=>['page'=>$this,'idx'=>'review','dataProvider'=>$this->model->searchComments(),'commentForm'=>$this->commentForm],
            ],
            self::QUESTION=>[
                'title'=>'<div class="title">'.Sii::t('sii','Questions').'</div>',
                'view'=>$this->controller->getThemeView('_product_questions'),
                'data'=>['page'=>$this,'idx'=>'question','dataProvider'=>$this->model->searchQuestions(),'questionForm'=>$this->questionForm],
            ],
        ];
    }    
    
    public function getCartItemForm()
    {
        $cartForm = new CartItemForm('addCart');
        $cartForm->product_id = $this->model->id;
        $cartForm->addCartUrl = $this->controller->getAddCartUrl($this);
        $cartForm->addCartScript = $this->controller->loadAddCartScript($this);
        if ($this->model->hasCampaign()){
            $cartForm->quantity = $this->model->getCampaign()->buy_x_qty;
        }
        return $cartForm;
    }
    
    public function getLikeForm($modal=false)
    {
        $likeForm = new LikeForm(get_class($this->model),$this->model->id);
        if ($modal)
            $likeForm->modal = true;        
        else
            $likeForm->formObject = '$("#cart-form")';
        
        $likeForm->formScript = $this->controller->loadProductLikeScript($this,'$(this).parent()');
        $likeForm->buttonScript = $this->controller->loadProductLikeScript($this,$likeForm->formObject);
        return $likeForm;
    }
    
    public function getCommentForm($id=null)
    {
        $commentForm = new CommentForm(CommentForm::SCENARIO_COUNTER,get_class($this->model),$this->model->id);
        $commentForm->formScript = $this->controller->loadProductCommentScript($this);
        $commentForm->signInScript = $this->controller->loadSigninScript($this,$this->getProductPageUrl(static::COMMENT));
        if (isset($id))
            $commentForm->id = 'comment-modal-form';
        
        return $commentForm;
    }
    
    public function getQuestionForm()
    {
        $questionForm = new QuestionForm();
        $questionForm->id = 'product_question_form';
        //todo To verify which one is used
        //$questionForm->askUrl = $this->model->url.'#questions';//this is used for return url for login
        $questionForm->askUrl = $this->getProductPageUrl(static::QUESTION);//this is used for return url for login
        $questionForm->obj_type = get_class(Product::model());
        $questionForm->obj_id = $this->model->id;
        $questionForm->formScript = $this->controller->loadQuestionScript($this);
        $questionForm->signInScript = $this->controller->loadSigninScript($this,$this->getProductPageUrl(static::QUESTION));
        return $questionForm;
    }
    /**
     * Product url (auto switching between direct and quick (modal) view 
     * @param boolean $modal indicate if use modal page url
     * @param array $params indicate if any uri params to be attached to the url
     * @return string
     */
    public function getProductUrl($modal=false,$params=[])
    {
        if ($modal)
            $route = static::trimPageId(ShopPage::PRODUCT).'/'.$this->model->slug;
        else
            $route = static::trimPageId(ShopPage::PRODUCTS).'/'.$this->model->slug;

        return $this->constructUrl($route,$params);
    }    
    /**
     * Always direct url for child page
     * @param type $subpage indicate if there is a product subpage e.g. comment tab, question tab
     * @return type
     */
    public function getProductPageUrl($subpage=null)
    {
        return $this->getProductUrl().'#'.$subpage;
    }

    public function getPageSeoTitle($locale)
    {
        return $this->model->getSeoTitle($locale);
    }
    
    public function getPageSeoDesc($locale)
    {
        return $this->model->getSeoDesc($locale);
    }
    
    public function getPageSeoKeywords()
    {
        return $this->model->getSeoKeywords();
    }
}
