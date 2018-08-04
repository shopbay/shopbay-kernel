<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopViewPage");
/**
 * Description of CampaignPage
 *
 * @author kwlok
 */
class CampaignPage extends ShopViewPage 
{
    const MODAL     = 'campaign_modal_page';
    const QUESTION  = 'campaign_question_page';
    const COMMENT   = 'campaign_comment_page';
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
                'view'=>$this->controller->getThemeView('_promo_modalview'),
                'data'=>[
                    'campaign'=>$this->model,
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
                'view'=>$this->controller->getThemeView('_promo_view'),
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
        $cartForm->addCartUrl = $this->controller->getAddCartUrl($this);
        $cartForm->addCartScript = $this->controller->loadAddCartScript($this);
        $cartForm->product_id = $this->model->x_product->id;
        $cartForm->quantity = $this->model->buy_x_qty;
        return $cartForm;
    }
    public function getLikeForm()
    {
        $likeForm = new LikeForm(get_class($this->model),$this->model->id);
        $likeForm->formScript = $this->controller->loadProductLikeScript($this,'$(this).parent()');
        $likeForm->buttonScript = $this->controller->loadProductLikeScript($this,$likeForm->formObject);
        return $likeForm;
    }  
    
    public function getCommentForm($id=null)
    {
        $commentForm = new CommentForm(CommentForm::SCENARIO_COUNTER,get_class($this->model),$this->model->id);
        $commentForm->formScript = $this->controller->loadProductCommentScript($this);
        $commentForm->signInScript = $this->controller->loadSigninScript($this,$this->getCampaignPageUrl(static::COMMENT));
        if (isset($id))
            $commentForm->id = 'comment-modal-form';
        
        return $commentForm;
    }    
    public function getQuestionForm()
    {
        $questionForm = new QuestionForm();
        $questionForm->id = 'campaign_question_form';
        $questionForm->askUrl = $this->getCampaignPageUrl(static::QUESTION);//this is used for return url for login
        $questionForm->obj_type = get_class($this->model);
        $questionForm->obj_id = $this->model->id;
        $questionForm->formScript = $this->controller->loadQuestionScript($this);
        $questionForm->signInScript = $this->controller->loadSigninScript($this,$this->getCampaignPageUrl(static::QUESTION));
        return $questionForm;
    }    
    /**
     * Campaign url (follows underlying model url) 
     * @param array $params indicate if any uri params to be attached to the url
     * @return string
     */
    public function getCampaignUrl($params=[])
    {
        $url = $this->model->getUrl($this->https);
        $uri = http_build_query(array_merge($params,$this->extraQueryParams));
        if (!empty($uri))
            $url .= '?'.$uri;
        return $url;
    }    
    /**
     * Always direct url for child page
     * @param type $subpage indicate if there is a subpage e.g. comment tab, question tab
     * @return type
     */
    public function getCampaignPageUrl($subpage=null)
    {
        return $this->getCampaignUrl().'#'.$subpage;
    }    
    
    public function getPageSeoTitle($locale)
    {
        return $this->model->getCampaignText($locale);
    }
    
}
