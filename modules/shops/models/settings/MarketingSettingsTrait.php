<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MarketingSettingsTrait
 *
 * @author kwlok
 */
trait MarketingSettingsTrait  
{
    public $socialMediaShare = 0;//default no
    /**
     * array value = $_GET['tabs_added'] return from facebook
     * array(
     *   'tabs_added' => array
     *       (
     *          441198302715307 => '1'
     *        )
     * );
     */
    public $fbPageData;
    public $fbPageLink;
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['fbPageData', 'ruleFacebookPage'],
            ['socialMediaShare', 'required'],
            ['socialMediaShare', 'boolean'],
        ]);
    }    
    public function ruleFacebookPage($attribute,$params)
    {
        if (!empty($this->fbPageData) && !$this->isFbPageLinked)
            $this->addError($attribute,Sii::t('sii','Facebook page link error'));
    }
    /**
     * @return the facebook page status return from facebook; Expected value is "1"
     */
    public function getIsFbPageLinked()
    {
        if (empty($this->fbPageData))
            return false;//return false if data not available
        if ($this->fbPageId==false)
            return false;//return false if page id is false
        
        $data = array_values($this->fbPageData);
        return $data[0]==1;
    }
    public function getFbPageAppLink()
    {
        return $this->fbPageLink.'?sk=app_'.$this->fbPageAppId;
    }
    public function getFbPageAppId()
    {
        return param('FACEBOOK_PAGEAPP_ID');
    }
    /**
     * @return the facebook page id
     */
    public function getFbPageId()
    {
        if (empty($this->fbPageData))
            return false;//return false if data not available
        
        $data = array_keys($this->fbPageData);
        return $data[0];
    }
    
    public function lookupFbPageLink($tabsData)
    {
        $this->fbPageData = $tabsData;
        $fbApi = new FbPageApi();
        return $fbApi->getPageLink($this->fbPageId);
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'fbPageData' => Sii::t('sii','Add Shop to Facebook Page'),
            'fbPageLink' => Sii::t('sii','View Facebook Shop'),
            'socialMediaShare' => Sii::t('sii','Display social media share button'),
        ]);
    }
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),[
            'fbPageData'=>$this->isFbPageLinked?Sii::t('sii','Yes'):Sii::t('sii','No'),
            'socialMediaShare'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->socialMediaShare)),
        ]);
    }     
    /**
     * @return array customized attribute values that do not display
     */
    public function attributeDisplayNone()
    {
        return array_merge(parent::attributeDisplayNone(),[
            'fbPageLink',
        ]);
    }      
    /**
     * @return boolean
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_marketing';
    }      
    /**
     * @return boolean
     */
    public function subFormViewFile()
    {
        return 'shops.views.settings._form_fbshop';
    }      
    
    public function renderSubForm($controller,$subFormId='settings_form_2')
    {
        $controller->beginWidget('CActiveForm',['id'=>$subFormId,'action'=>url('shop/settings/marketing?service='.Feature::$addShopToFacebookPage)]); 

        echo CHtml::hiddenField('Subscription[service]',Feature::$addShopToFacebookPage);

        $controller->renderPartial($this->subFormViewFile(),['model'=>$this,'formId'=>$subFormId]);

        $controller->endWidget();                
    }    
    /**
     * OVERRIDDEN
     * This render the setting form
     * @param type $controller The controller used to render the form
     */
    public function renderActiveForm($controller)
    {
        $form = $controller->beginWidget('CActiveForm', ['id'=>'settings_form','action'=>url('shop/settings/marketing?service='.Feature::$hasSocialMediaShareButton)]);

        echo CHtml::hiddenField('Subscription[service]',Feature::$hasSocialMediaShareButton);

        echo $form->errorSummary($this); 

        $this->renderForm($controller);

        echo '<div class="row" style="padding-top:20px;clear:left">';

        $controller->widget('zii.widgets.jui.CJuiButton',[
            'name'=>'actionButtonForm1',
            'buttonType'=>'button',
            'caption'=>Sii::t('sii','Save'),
            'value'=>'actionbtn',
            'onclick'=>'js:function(){servicepostcheck("settings_form");}',
        ]);

        echo '</div>'; 

        $controller->endWidget();         
    }     
}

/**
 * A simple facebook graph api explorer for facebook page
 */
class FbPageApi 
{
    public function getPageLink($pageId)
    {
        $graph_url= "https://graph.facebook.com/".$pageId."?access_token=".$this->_getAccessToken();
        $output = json_decode($this->_curl($graph_url),true);
        if (isset($output['error'])){
            //need to handle this error:
            //array (
            //  'error' => 
            //  array (
            //    'message' => 'An access token is required to request this resource.',
            //    'type' => 'OAuthException',
            //    'code' => 104,
            //  ),
            //)
            //Second error: page not published
            //{
            //  "error": {
            //    "message": "Unsupported get request. Object with ID '441198302715307' does not exist, cannot be loaded due to missing permissions, or does not support this operation. Please read the Graph API documentation at https://developers.facebook.com/docs/graph-api",
            //    "type": "GraphMethodException",
            //    "code": 100,
            //    "fbtrace_id": "FTswGFSYwD4"
            //  }
            //}
            logError(__METHOD__.' error facebook graph '.$graph_url, $output['error']);
            throw new CException($output['error']['message']);
        }
        elseif (isset($output['link'])){
            // Expected output from facebook:
            // array (
            //  'id' => '<page id>',
            //  'can_post' => false,
            //  'category' => 'Product/Service',
            //  'checkins' => 0,
            //  'has_added_app' => false,
            //  'is_community_page' => false,
            //  'is_published' => true,
            //  'likes' => 1,
            //  'link' => 'https://www.facebook.com/pages/<page slug>/<page id>',
            //  'name' => '<page name>',
            ///  'talking_about_count' => 0,
            //  'were_here_count' => 0,
            //)
            logInfo(__METHOD__.' facebook graph '.$graph_url, $output);
            return $output['link'];
        }
        else {
            logError(__METHOD__.' unknown output from facebook graph '.$graph_url, $output);
            throw new CException(Sii::t('sii','Unknown response from Facebook'));
        }
    }
        
    private function _getAccessToken()
    {
        $graph_url= "https://graph.facebook.com/oauth/access_token?client_id=".param('FACEBOOK_PAGEAPP_ID')."&client_secret=".param('FACEBOOK_PAGEAPP_SECRET')."&grant_type=client_credentials";
        //return format
        //access_token={app_id}|{app_secret}
        $output = explode('=',$this->_curl($graph_url));
        if (!empty($output)){
            logInfo(__METHOD__.' ok');
            return $output[1];
        }
        else 
            return null;
    }
    
    private function _curl($endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);        
        curl_close($ch);           
        return $response;
    }
        
}