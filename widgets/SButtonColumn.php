<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('zii.widgets.grid.CButtonColumn');
/**
 * Description of SButtonColumn
 * [1] Customized to have client javascript registered only when button is visible
 * [2] Have buttons register for central management
 * 
 * @author kwlok
 */
class SButtonColumn extends CButtonColumn 
{
    public $showScript;
    /**
     * Initializes the column.
     * This method registers necessary client script for the button column.
     * 
     * Customize not to call registerClientScript() here instead of at renderDataCellContent()
     * This is because $showScript data is not ready during init(), but only after renderButton()
     *  
     * @override
     */
    public function init()
    {
        $this->initDefaultButtons();
        
        foreach($this->buttons as $id=>$button)
        {
                if(strpos($this->template,'{'.$id.'}')===false)
                        unset($this->buttons[$id]);
                elseif(isset($button['click']))
                {
                        if(!isset($button['options']['class']))
                                $this->buttons[$id]['options']['class']=$id;
                        if(!($button['click'] instanceof CJavaScriptExpression))
                                $this->buttons[$id]['click']=new CJavaScriptExpression($button['click']);
                }
        }
        //--------//
        //customization starts here
        $this->showScript = new CMap();
        //--------//
    }
    /**
     * @override
     * @inheritdoc
     */
    protected function registerClientScript()
    {
        $js=array();
        $ids=array();//stores unique js key
        foreach($this->buttons as $id=>$button){
            
            if(isset($button['click']) && $this->showScript->itemAt($id)){//check if showScript is true                
                $function=CJavaScript::encode($button['click']);
                $class=preg_replace('/\s+/','.',$button['options']['class']);
                $js[]="jQuery(document).on('click','#{$this->grid->id} a.{$class}',$function);";
                $ids[]=$id;//store $id to assign registerScript later unique key
            }
        }

        if($js!==array()){
            foreach($js as $key => $script) {
                Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$ids[$key], $script);
            }
        }
    }  
    /**
     * Customize to call registerClientScript() here instead of at init()
     * 
     * @override
     * @inheritdoc
     */
    protected function renderDataCellContent($row,$data)
    {
        parent::renderDataCellContent($row, $data);
        //--------//
        //customization starts here
        $this->registerClientScript();
        //--------//        
    }
    /**
     * @override
     * @inheritdoc
     */
    protected function renderButton($id,$button,$row,$data)
    {
        //--------//
        //customization starts here        
        if (isset($button['visible']))
            $this->showScript->add($id,$this->evaluateExpression($button['visible'],array('row'=>$row,'data'=>$data)));
        //--------//        
        parent::renderButton($id, $button, $row, $data);
    }        
    /**
     * Return item buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @param type $mode Support two modes. Null for default mode, non-null for another mode
     * @return type
     */
    public static function getItemButtons($buttons,$mode=null)
    {
        return self::_getButtons(self::getItemButtonRegister($mode), $buttons);
    }    
    /**
     * Return order buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getOrderButtons($buttons)
    {
        return self::_getButtons(self::getOrderButtonRegister(), $buttons);
    }
    /**
     * Return shipping buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getShippingButtons($buttons)
    {
        return self::_getButtons(self::getShippingButtonRegister('shipping'), $buttons);
    }
    /**
     * Return shipping zone buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getZoneButtons($buttons)
    {
        return self::_getButtons(self::getShippingButtonRegister('zone'), $buttons);
    }
    /**
     * Return tax buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getTaxButtons($buttons)
    {
        return self::_getButtons(self::getTaxButtonRegister(), $buttons);
    }    
    /**
     * Return campaign buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getCampaignButtons($buttons)
    {
        return self::_getButtons(self::getCampaignButtonRegister(), $buttons);
    }
    /**
     * Return question buttons
     * 
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    public static function getQuestionButtons($buttons)
    {
        return self::_getButtons(self::getQuestionButtonRegister(), $buttons);
    }
    /**
     * Return order buttons
     * 
     * @param type $register Button register
     * @param type $buttons Array names of desired buttons
     * @return type
     */
    private static function _getButtons($register,$buttons)
    {
        $result = array();
        foreach ($buttons as $button => $visible) {
            if (isset($register[$button])){
                $register[$button] = array_merge($register[$button], array('visible'=>is_string($visible)?$visible:'true'));
                $result = array_merge($result,array($button=>$register[$button]));
            }
                
        }
        return $result;
    }      
    /**
     * Item Buttons Register
     * 
     * @param type $mode Support two modes. Null for default mode, non-null for another mode
     * @return array 
     */
    public static function getItemButtonRegister($mode=null)
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
            ),
            'review' => array(
                'label'=>self::getButtonLabel('update',Sii::t('sii','Write a review')), 
                'imageUrl'=>false,  
                'url'=>isset($mode)?'$data->viewUrl':'\'javascript:void(0)\'', 
            ),
            'receive' => array(
                'label'=>self::getButtonLabel('receive',Sii::t('sii','Receive Item')), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_RECEIVE,//for tasks.js use
                ),
            ),
            'pick' => array(
                'label'=>self::getButtonLabel('pick'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_PICK,//for tasks.js use
                ),
            ),
            'pack' => array(
                'label'=>self::getButtonLabel('pack'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_PACK,//for tasks.js use
                ),
            ),
            'ship' => array(
                'label'=>self::getButtonLabel('ship'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_SHIP,//for tasks.js use
                ),
            ),
            'process_item' => array( //direct call ship
                'label'=>self::getButtonLabel('process_item'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_PROCESS,//for tasks.js use
                ),
            ),                     
            'return' => array(
                'label'=>self::getButtonLabel('return'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//tasks.js _w() refresh, for gridview afterAjaxUpdate use?
                    'data-action'=>WorkflowManager::ACTION_RETURNITEM,//for tasks.js use
                ),
            ),
            'refund' => array(
                'label'=>self::getButtonLabel('refund'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wi($(this));}',
                'options'=>array(
                    'fn'=>'wi',//for gridview afterAjaxUpdate use
                    'data-action'=>WorkflowManager::ACTION_REFUND,//for tasks.js use
                ),
            ),                     
            'rollback' => array(
                'label'=>self::getButtonLabel('rollback',Sii::t('sii','Rollback Item')), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){if (!confirm("'.Sii::t('sii','Are you sure you want to rollback this item?').'")) return false; r($(this));}',
                'options'=>array(
                    'data-type'=>'item',//for tasks.js method r() use
                ),
            ),
            'stockmanage' => array(
                'label'=>self::getButtonLabel('inventory',Sii::t('sii','Manage Inventories')), 
                'imageUrl'=>false,  
                'url'=>'url(\'product/stock\',array(\'pid\'=>$data->getProductId()))',
            ),
            'tracking' => array(
                'label'=>self::getButtonLabel('ship',Sii::t('sii','Track Shipment')), 
                'imageUrl'=>false,  
                'url'=>'$data->tracking_url', 
                'options'=>array('target'=>'_blank'),
            ),            
        );
    }   
    /**
     * Order Buttons Register
     * 
     * @return array 
     */
    public static function getOrderButtonRegister()
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
            ),
            'contact' => array(
                'label'=>self::getButtonLabel('contact'), 
                'imageUrl'=>false,  
                'url'=>'$data->contactMerchantUrl', 
            ),
            'pay' => array(
                'label'=>self::getButtonLabel('pay'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wo($(this));}',
                'options'=>array(
                    'fn'=>'wo',//for gridview afterAjaxUpdate use, and wrb(id)
                    'data-action'=>WorkflowManager::ACTION_PAY,//for tasks.js use
                ),
            ),              
            'repay' => array(
                'label'=>self::getButtonLabel('repay'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wo($(this));}',
                'options'=>array(
                    'fn'=>'wo',//for gridview afterAjaxUpdate use, and wrb(id)
                    'data-action'=>WorkflowManager::ACTION_REPAY,//for tasks.js use
                ),
            ),              
            'process' => array(
                'label'=>self::getButtonLabel('process'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wom($(this));}',
                'options'=>array(
                    'fn'=>'wom',//for gridview afterAjaxUpdate use, and wrb(id)
                    'data-action'=>WorkflowManager::ACTION_PROCESS,//for tasks.js use
                ),
            ),                     
            'verify' => array(
                'label'=>self::getButtonLabel('verify',Sii::t('sii','Verify Payment')), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wo($(this));}',
                'options'=>array(
                    'fn'=>'wo',//for gridview afterAjaxUpdate use, and wrb(id)
                    'data-action'=>WorkflowManager::ACTION_VERIFY,//for tasks.js use
                ),
            ),                     
            'refund' => array(
                'label'=>self::getButtonLabel('refund'), 
                'imageUrl'=>false,  
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){wom($(this));}',
                'options'=>array(
                    'fn'=>'wom',//for gridview afterAjaxUpdate use, and wrb(id)
                    'data-action'=>WorkflowManager::ACTION_REFUND,//for tasks.js use
                ),
            ),                     
        );
    }
    /**
     * Shipping Buttons Register
     * 
     * @return array 
     */
    public static function getShippingButtonRegister($target='shipping')
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
            ),  
            'update' => array(
                'label'=>self::getButtonLabel('update',$target=='zone'?Sii::t('sii','Update Zone'):Sii::t('sii','Update Shipping')), 
                'imageUrl'=>false,  
                'visible'=>'$data->updatable()', 
            ),                                    
            'delete' => array(
                'label'=>self::getButtonLabel('delete',$target=='zone'?Sii::t('sii','Delete Zone'):Sii::t('sii','Delete Shipping')), 
                'imageUrl'=>false,  
                'visible'=>'$data->deletable()', 
                'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':nth-child(1)\').text()+"?")) return false;}',  
            ),                                    
        );
    }
    /**
     * Tax Buttons Register
     * 
     * @return array 
     */
    public static function getTaxButtonRegister()
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
            ),  
            'update' => array(
                'label'=>self::getButtonLabel('update',Sii::t('sii','Update Tax')), 
                'imageUrl'=>false,  
                'visible'=>'$data->updatable()', 
            ),                                    
            'delete' => array(
                'label'=>self::getButtonLabel('delete',Sii::t('sii','Delete Tax')), 
                'imageUrl'=>false,  
                'visible'=>'$data->deletable()', 
                'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':nth-child(1)\').text()+"?")) return false;}',  
            ),                                    
        );
    }    
    /**
     * Campaign Buttons Register
     * 
     * @return array 
     */
    public static function getCampaignButtonRegister()
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
            ),  
            'update' => array(
                'label'=>self::getButtonLabel('update',Sii::t('sii','Update Campaign')), 
                'imageUrl'=>false,  
                'visible'=>'$data->updatable()', 
            ),                                    
            'delete' => array(
                'label'=>self::getButtonLabel('delete',Sii::t('sii','Delete Campaign')), 
                'imageUrl'=>false,  
                'visible'=>'$data->deletable()', 
                'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':nth-child(2)\').text()+"?")) return false;}',  
            ),                                    
        );
    }
    /**
     * Question Buttons Register
     * 
     * @return array 
     */
    public static function getQuestionButtonRegister()
    {
        return array (
            'view' => array(
                'label'=>self::getButtonLabel('view'), 
                'imageUrl'=>false,  
                'url'=>'$data->viewUrl', 
                'visible'=>'!$data->answerable()',
            ),
            'answer' => array(
                'label'=>self::getButtonLabel('answer'), 
                'imageUrl'=>false,   
                'url'=>'\'javascript:void(0)\'', 
                'click'=>'function(){q($(this));}',
                'visible'=>'$data->answerable()',
            ),
         );
    }
    /**
     * Return button label (icon + title)
     * @param type $button
     * @param type $title
     * @return type
     */    
    public static function getButtonLabel($button,$title=null)
    {
        if (!isset($title))
            $title = self::getButtonTitle($button);
        return str_replace('><', ' title="'.$title.'"><', self::getButtonIcon($button));
    }
    /**
     * Return button tooltip (icon + tooltip)
     * @param type $button
     * @param type $title
     * @return type
     */    
    public static function getButtonToolTip($button,$content,$config=array('cssClass'=>'tooltip-content'))
    {
        return Yii::app()->controller->widget('common.widgets.stooltip.SToolTip',array(
            'symbol'=>self::getButtonIcon($button),
            'content'=>$content,
            'autoTop'=>false,
            'config'=>$config),true);
    }
    
    public static function getButtonIcon($button)
    {
        switch (strtolower($button)) {
            case 'search':
                return '<i class="fa fa-search fa-fw"></i>';
            case 'create':
                return '<i class="fa fa-plus fa-fw"></i>';
            case 'write':
                return '<i class="fa fa-pencil fa-fw"></i>';
            case 'view':
                return '<i class="fa fa-info-circle fa-fw"></i>';
            case 'update':
            case 'edit':
            case 'answer':
                return '<i class="fa fa-edit fa-fw"></i>';
            case 'delete':
                return '<i class="fa fa-trash-o fa-fw"></i>';
            case 'shoptheme':
            case 'design':
                return '<i class="fa fa-paint-brush fa-fw"></i>';
            case 'account':
                return '<i class="fa fa-user fa-fw"></i>';
            case 'activate':
                return '<i class="fa fa-toggle-on fa-fw"></i>';
            case 'deactivate':
                return '<i class="fa fa-toggle-off fa-fw"></i>';
            case 'attribute':
                return '<i class="fa fa-tags fa-fw"></i>';
            case 'product':
                return '<i class="fa fa-barcode fa-fw"></i>';
            case 'category':
                return '<i class="fa fa-sitemap fa-fw"></i>';
            case 'import':
            case 'productimport':
                return '<i class="fa fa-upload fa-fw"></i>';
            case 'download':
                return '<i class="fa fa-download fa-fw"></i>';
            case 'account':
            case 'accountprofile':
                return '<i class="fa fa-user fa-fw"></i>';
            case 'brand':
                return '<i class="fa fa-flag fa-fw"></i>';
            case 'banner':
                return '<i class="fa fa-play-circle fa-fw"></i>';
            case 'inventory':
                return '<i class="fa fa-tasks fa-fw"></i>';
            case 'ask':
                return '<i class="fa fa-question fa-fw"></i>';
            case 'shipping':
                return '<i class="fa fa-truck fa-fw"></i>';
            case 'paymentmethod':
            case 'payment':
            case 'pay':
            case 'repay':
                return '<i class="fa fa-dollar fa-fw"></i>';
            case 'preview':
                return '<i class="fa fa-binoculars fa-fw"></i>';
            case 'save':
                return '<i class="fa fa-save fa-fw"></i>';
            case 'all':
                return '<i class="material-icons">all_inclusive</i>';
            case 'po':
                return '<i class="material-icons">content_copy</i>';
            case 'so':
                return '<i class="material-icons">insert_drive_file</i>';
            case 'items':
                return '<i class="material-icons">view_module</i>';
            case 'zone':
                return '<i class="material-icons">map</i>';
            case 'bga':
                return '<i class="material-icons">loyalty</i>';
            case 'sales':
                return '<i class="material-icons">local_offer</i>';
            case 'code':
                return '<i class="material-icons">code</i>';
            case 'question':
                return '<i class="material-icons">live_help</i>';
            case 'asked':
                return '<i class="material-icons">chat_bubble_outline</i>';
            case 'answered':
                return '<i class="material-icons">question_answer</i>';
            case 'unread':
                return '<i class="fa fa-envelope-o fa-fw"></i>';
            case 'sent':
                return '<i class="fa fa-send fa-fw"></i>';
            case 'low_stock':
                return '<i class="material-icons">priority_high</i>';
            case 'out_of_stock':
                return '<i class="material-icons">crop_7_5</i>';
            case 'page':
                return '<i class="material-icons">description</i>';
            case 'marketing':
                return '<i class="material-icons">share</i>';
            case 'chatbot':
                return '<i class="material-icons">chat</i>';
            case 'tax':
                return '<i class="fa fa-fw">%</i>';
            case 'pages':
                return '<i class="fa fa-file-text-o fa-fw"></i>';
            case 'campaign':
            case 'campaignbga':
            case 'campaignsale':
            case 'campaignpromocode':
                return '<i class="fa fa-bullseye fa-fw"></i>';
            case 'address':
                return '<i class="fa fa-map-marker fa-fw"></i>';
            case 'contact':
                return '<i class="fa fa-envelope-o fa-fw"></i>';
            case 'inbox':
                return '<i class="fa fa-inbox fa-fw"></i>';
            case 'news':
                return '<i class="fa fa-bullhorn fa-fw"></i>';
            case 'question':
                return '<i class="fa fa-question fa-fw"></i>';
            case 'question-circle':
                return '<i class="fa fa-question-circle fa-fw"></i>';
            case 'sitemap':
                return '<i class="fa fa-sitemap fa-fw"></i>';
            case 'reply':
                return '<i class="fa fa-reply fa-fw"></i>';
            case 'process':
            case 'process_item':
            case 'shopsetting':
            case 'settings':
                return '<i class="fa fa-gear fa-fw"></i>';
            case 'pick':
                return '<i class="fa fa-hand-o-right fa-fw"></i>';
            case 'pack':
                return '<i class="fa fa-cube fa-fw"></i>';
            case 'ship':
                return '<i class="fa fa-truck fa-fw"></i>';
            case 'receive':
            case 'verify':
                return '<i class="fa fa-check-square-o fa-fw"></i>';
            case 'return':
            case 'returnitem':
                return '<i class="fa fa-hand-o-left fa-fw"></i>';
            case 'refund':
                return '<i class="fa fa-mail-reply fa-fw"></i>';
            case 'cart':
                return '<i class="fa fa-shopping-cart fa-fw"></i>';
            case 'customer':
                return '<i class="fa fa-group fa-fw"></i>';
            case 'dashboard':
                return '<i class="fa fa-line-chart fa-fw"></i>';
            case 'submit':
                return '<i class="fa fa-level-up fa-fw"></i>';
            case 'publish':
                return '<i class="fa fa-share-square-o fa-fw"></i>';
            case 'adjust':
                return '<i class="fa fa-adjust fa-fw"></i>';
            case 'approve':
            case 'subscription':
                return '<i class="fa fa-check fa-fw"></i>';
            case 'pagelayout':
            case 'layout':
                return '<i class="fa fa-columns fa-fw"></i>';
            case 'tutorial':
                return '<i class="fa fa-file-text-o fa-fw"></i>';
            case 'tag':
                return '<i class="fa fa-tags fa-fw"></i>';
            case 'media':
                return '<i class="fa fa-file-o fa-fw"></i>';
            case 'navigate':
                return '<i class="fa fa-navicon fa-fw"></i>';
            case 'seo':
                return '<i class="fa fa-search-plus"></i>';
            case 'notify':
                return '<i class="fa fa-paper-plane-o fa-fw"></i>';
            case 'message':
                return '<i class="fa fa-envelope-o fa-fw"></i>';
            case 'help':
                return '<i class="fa fa-life-saver"></i>';
            case 'ticket':
                return '<i class="fa fa-ticket fa-fw"></i>';
            case 'email':
                return '<i class="fa fa-at fa-fw"></i>';
            case 'credit-card':
                return '<i class="fa fa-credit-card fa-fw"></i>';
            case 'password':
                return '<i class="fa fa-lock fa-fw"></i>';
            case 'history':
                return '<i class="fa fa-history"></i>';
            case 'invite':
                return '<i class="fa fa-share-alt fa-fw"></i>';
            case 'rollback':
                return '<i class="fa fa-undo fa-fw"></i>';
            case 'interrupt':
                return '<i class="fa fa-magic fa-fw"></i>';
            default:
                return '<span class="letter-icon">'.strtoupper(substr($button, 0, 1)).'</span>';
        }
    }
    
    public static function getButtonTitle($button)
    {
        switch (strtolower($button)) {
            case 'view':
                return Sii::t('sii','More information');
            case 'pick':
                return Sii::t('sii','Pick Item');
            case 'pack':
                return Sii::t('sii','Pack Item');
            case 'ship':
                return Sii::t('sii','Ship Item');
            case 'return':
                return Sii::t('sii','Return Item');
            case 'answer':
                return Sii::t('sii','Write an answer');
            case 'pay':
                return Sii::t('sii','Pay Order');
            case 'repay':
                return Sii::t('sii','Repay Order');
            case 'process':
                return Sii::t('sii','Process Shipping Order');
            case 'process-po':
                return Sii::t('sii','Process Purchase Order');
            case 'process_item':
                return Sii::t('sii','Process Item');
            case 'receive':
                return Sii::t('sii','Receive');
            case 'returnitem':
                return Sii::t('sii','Return');
            case 'refund':
                return Sii::t('sii','Refund');
            case 'contact':
                return Sii::t('sii','Contact Merchant');
            default:
                return Sii::t('sii','undefined');
        }
    }
    
    public static function getButtonSubscript($button)
    {
        switch (strtolower($button)) {
            case 'pay':
                return Sii::t('sii','pay');
            case 'repay':
                return Sii::t('sii','repay');
            case 'process':
            case 'process_item':
                return Sii::t('sii','process');
            case 'pick':
                return Sii::t('sii','pick');
            case 'pack':
                return Sii::t('sii','pack');
            case 'ship':
                return Sii::t('sii','ship');
            case 'receive':
                return Sii::t('sii','receive');
            case 'refund':
                return Sii::t('sii','refund');
            case 'returnitem':
                return Sii::t('sii','return');
            default:
                return Sii::t('sii','undefined');
        }
    }
    
}
