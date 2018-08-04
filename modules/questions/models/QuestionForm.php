<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of QuestionForm
 *
 * @author kwlok
 */
class QuestionForm extends SFormModel 
{   
    public $id='question_form';
    public $page=1;
    public $type=Question::TYPE_PUBLIC;
    public $question;
    public $question_time;
    public $obj_type;
    public $obj_id;
    public $askUrl;
    public $formView;
    public $formScript = 'postquestion($(this).attr(\'form\'));';
    public $signInScript = 'signin();';
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Question');
    } 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('question, obj_type, obj_id', 'required'),
            array('obj_id, type', 'numerical', 'integerOnly'=>true),
            array('question','rulePurify'),
            array('obj_type', 'length', 'max'=>20),
            array('question', 'length', 'max'=>5000),
            array('product_id', 'required', 'on'=>'prevquestion'),
            array('product_id, type, question, page, askUrl', 'safe'),            
        );
    } 
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'obj_type' => Sii::t('sii','Object Type'),
            'obj_id' => Sii::t('sii','Object Id'),
            'question' => Sii::t('sii','Question'),
            'type' => Sii::t('sii','I want to share this question to public'),
        );
    }       
    
    public function getShopName()
    {        
        if ($this->hasShop()){
            $shop = $this->getShop();
            if ($shop!=null)
                return CHtml::link($shop->displayLanguageValue('name',user()->getLocale()),$shop->url);
        }
        return Sii::t('sii','Shop not found');
    }
    
    public function hasShop()
    {        
        return $this->obj_type==get_class(Shop::model());
    } 

    public function getShop()
    {        
        if ($this->hasShop())
            return Shop::model()->findByPk($this->obj_id);
        else
            return null;
    }
    
    public function hasProduct()
    {        
        return $this->obj_type==get_class(Product::model());
    }    
    
    public function getProduct()
    {        
        if ($this->hasProduct())
            return Product::model()->findByPk($this->obj_id);
        else
            return null;
    }
    
}