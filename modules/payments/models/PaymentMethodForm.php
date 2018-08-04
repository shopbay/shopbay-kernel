<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.payments.behaviors.PaymentMethodBehavior");
/**
 * Description of PaymentMethodForm
 *
 * @author kwlok
 */
class PaymentMethodForm extends LanguageMasterForm
{
    /*
     * Inherited attributes
     */
    public $model = 'PaymentMethod';
    protected $slaveFormAttribute = 'subForm';
    /*
     * Local attributes
     */
    public $name;
    public $method;
    public $params;
    public $status;
    public $create_time;
    public $subForm;//extra field
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'paymentmethodbehavior' => [
                'class'=>'PaymentMethodBehavior',
            ],
        ]);
    }  
    /**
     * 
     * @return type
     */
    public function localeAttributes() 
    {
        return [
            'name'=>[
                'required'=>true,
                'inputType'=>'textField',
                'inputHtmlOptions'=>['size'=>50,'maxlength'=>50],
            ],
        ];
    }
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['name, method, params', 'required'],
            ['method', 'numerical', 'integerOnly'=>true],
            ['id, name, method, params, status, create_time', 'safe'],
        ]);
    }     
    /**
     * Always invoke slave form validation
     * @return boolean
     */
    public function invokeSlaveFormValidation() 
    {
        return true;//always true; no condition
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'method' => Sii::t('sii','Payment Method'),
        ]);
    }
    public function getAvailableMethods($locale=null)
    {
        $names = PaymentMethod::getNames($locale);
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['shop_id'=>$this->shop_id]);
        $methods = PaymentMethod::model()->findAll($criteria);
        foreach ($methods as $method) {
            if ($method->method!=PaymentMethod::OFFLINE_PAYMENT){
                if (array_key_exists($method->method, $names))
                    unset($names[$method->method]);
            }
        }
        return $names;
    }  
    /**
     * Load subForm instance; Create one if not exists
     * @return type
     */
    public function loadSubForm()
    {
        if ($this->subForm==null)
            $this->subForm = self::createSubFormInstance($this->method,$this->shop_id);
        return $this->subForm;
    }
    /**
     * Load subForm attributes from model instance
     */
    public function loadSubFormAttributes()
    {
        $this->subForm = $this->loadSubForm();
        $this->subForm->attributes = $this->modelInstance->attributes;
        foreach ($this->subForm->paramsAttributes() as $attribute) {
            $this->subForm->{$attribute} = $this->getParamsAttributeAsString($attribute);
        }
        //logTraceDump(__METHOD__.' '.get_class($this),$this->attributes);
        return $this->subForm;
    }
    /**
     * Set subForm attributes 
     * @param type $attributes Attributes to be set
     */
    public function setSubFormAttributes($attributes,$json=false)
    {
        $this->subForm = $this->loadSubForm();
        $this->subForm->assignLocaleAttributes($attributes,$json);
        //[1] set shop id
        $this->subForm->shop_id = $this->shop_id;
        //[2] inherit payment method name from custom payment
        if (is_array($this->subForm->name))
            $this->name = json_encode($this->subForm->name);
        else
            $this->name = $this->subForm->name;
        //[3] set params
        $this->params = $this->subForm->params;
        //logTraceDump(__METHOD__.' '.get_class($this),$this->attributes);  
    }
    /**
     * Set model attributes
     * @return \PaymentMethodForm
     */
    public function setModelAttributes()
    {
        $this->modelInstance->attributes = $this->getAttributes();
        if ($this->modelInstance->method==PaymentMethod::OFFLINE_PAYMENT)
            $this->modelInstance->method = $this->subForm->mode;
        //logTrace(__METHOD__.' '.get_class($this->modelInstance), $this->modelInstance->attributes);
        return $this;
    }      
    /**
     * Return status text
     * @param type $color
     * @return type
     */
    public function getStatusText($color=true)
    {
        return $this->modelInstance->getStatusText($color);
    }
        
    public static function createSubFormInstance($method,$shopId,$scenario=null)
    {
        $form = PaymentMethod::getFormInstance($method,$scenario);
        $form->loadLocaleAttributeTemplates($shopId);
        return isset($form)?$form:null;
    }
    
    public static function getNote1()
    {
        return Sii::t('sii','Below is the template message will be displayed on the order confirmation page after customer has successfuly placed their order.');
    }
    
    public static function getNote2()
    {
        return Sii::t('sii','You may modify the message to suit your needs.');
    }

}