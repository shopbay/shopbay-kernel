<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SPageFilterForm
 *
 * @author kwlok
 */
abstract class SPageFilterForm extends CFormModel
{
    /*
     * Type list
     */
    CONST TYPE_TEXTFIELD           = 'textField';
    CONST TYPE_DROPDOWNLIST        = 'dropDownList';
    CONST TYPE_TEXTAREA            = 'textArea';
    CONST TYPE_DATE                = 'date';
    /*
     * Operator list
     */
    CONST OP_CONTAINS              = '~';//todo for future use; currently all text are search using LIKE operator, already supported "contains" in built
    CONST OP_EQUAL                 = '=';
    CONST OP_GREATER_THAN          = '>';
    CONST OP_GREATER_THAN_OR_EQUAL = '>=';
    CONST OP_LESS_THAN             = '<';
    CONST OP_LESS_THAN_OR_EQUAL    = '<=';
    CONST OP_NOT_EQUAL             = '<>';
    CONST OP_LAST_24_HOURS         = '1~';
    CONST OP_LAST_7_DAYS           = '7~';
    CONST OP_LAST_30_DAYS          = '30~';
    CONST OP_LAST_90_DAYS          = '90~';
    /*
     * Status list
     */
    CONST STATUS_ONLINE  = 'Online';
    CONST STATUS_OFFLINE = 'Offline';
    CONST STATUS_EXPIRED = 'Expired';
    CONST STATUS_APPROVED= 'Approved';
    CONST STATUS_REJECTED= 'Rejected';
    CONST STATUS_PENDING = 'Pending';
    /*
     * Meta attributes
     */
    public $formId = 'page_filter_form';
    public $actionUrl;
    public $ops = [];//submitted operators values
    /**
     * Fields config e.g.
     * [
     *   <field_1> => [<type>=>'textField',<ops>=[SPageFilterForm::OP_CONTAINS,SPageFilterForm::OP_GREATER_THAN]],
     *   <field_1> => [<type>=>'date',<ops>=[]],
     * ]
     * 
     * @var array 
     */
    public $config = [];
    /**
     * Initializes this model.
     */
    public function init()
    {
    }
    /**
     * Get form attributes (removing all the meta data attributes)
     * @return type
     */    
    public function getFields()
    {
        $fields = $this->getAttributes();
        unset($fields['formId']);
        unset($fields['actionUrl']);
        unset($fields['config']);
        unset($fields['ops']);
        return $fields;
    }
    /**
     * @return array field config
     */
    public function getConfig($field)
    {
        if (isset($this->config[$field]))
            return $this->config[$field];
        else
            return null;
    }
    /**
     * Render field html element according to its type
     */
    public function renderField($field,$value)
    {
        $html = CHtml::label($this->getAttributeLabel($field),get_class($this));
        $config = $this->getConfig($field);
        if (is_array($config) && isset($config['type'])){
            //render op field
            if (isset($config['ops'])){
                $html .= CHtml::dropDownList($this->getFieldOpName($field),$this->getFieldOpValue($field),$this->getOperators($config['ops'])); 
            }
            //render type field
            switch ($config['type']) {
                case self::TYPE_TEXTFIELD:
                    $html .= CHtml::textField($field, $value, $this->parseHtmlOptions($config, $value));
                    cs()->registerScript($field,"pagefiltertextonchange('$this->widgetCssClass','$this->formId','$field');");
                    break;
                case self::TYPE_TEXTAREA:
                    $html .= CHtml::textArea($field, $value, $this->parseHtmlOptions($config, $value));
                    cs()->registerScript($field,"pagefiltertextonchange('$this->widgetCssClass','$this->formId','$field');");
                    break;
                case self::TYPE_DATE:
                    $htmlOptions = $this->parseHtmlOptions($config, $value);
                    $autoSubmitOps = json_encode([
                        self::OP_LAST_24_HOURS=>1,
                        self::OP_LAST_7_DAYS=>7,
                        self::OP_LAST_30_DAYS=>30,
                        self::OP_LAST_90_DAYS=>90,
                     ]);
                    $htmlOptions['data-auto-submit-ops'] = $autoSubmitOps;
                    $htmlOptions['data-icon'] = Yii::app()->controller->getImage('datepicker',false);
                    $html .= Yii::app()->controller->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'name'=>$field,
                                'value'=>$value,
                                // additional javascript options for the date picker plugin
                                'options'=>array(
                                    'showAnim'=>'fold',
                                    'showOn'=>'both',
                                    'changeMonth'=>true,
                                    'changeYear'=>true,
                                    //'yearRange'=>'2016:2026',
                                    'dateFormat'=> 'yy-mm-dd',
                                    'gotoCurrent'=>true,
                                    'buttonImage'=> $htmlOptions['data-icon'],
                                    'buttonImageOnly'=> true,
                                ),
                                'htmlOptions'=>$htmlOptions,
                            ),true);
                    cs()->registerScript($field,"pagefilterdateonchange('$this->widgetCssClass','$this->formId','$field','$autoSubmitOps');");
                    break;
                case self::TYPE_DROPDOWNLIST:
                    $html .= CHtml::dropDownList($field,$value,isset($config['selectOptions']) ? $config['selectOptions'] : [], $this->parseHtmlOptions($config, $value)); 
                    cs()->registerScript($field,"pagefilterselectonchange('$this->widgetCssClass','$this->formId','$field');");
                    break;
                default:
                    break;
            }
            
        }
        else //default field display
            $html = CHtml::textField($field, $value, ['maxlength'=>10]);
        
        return $html;
    }
    /**
     * Perform form validation 
     * (1) Extra care is to remove operators from attribute value before validation
     * (2) Extra care is to verify status
     * @see spagefilter.js for prepending operator into attribute
     */
    public function validateFields()
    {
        logTrace(__METHOD__.' before',$this->fields);
        foreach ($this->fields as $field => $value) {
            $config = $this->getConfig($field);
            if (isset($config['ops'])){
                $op = $this->parseFieldOpValue($field);
                if (!empty($op)){//meaning containing operator
                    $this->$field = substr($this->$field, strlen($op));//remove operator
                    $this->ops[$field] = $op;//memorize the submitted operator
                }
            }
        }
        $this->validate();//perform normal validation after operators are removed
        if ($this->hasErrors())
            logError(__METHOD__.' validation errors',$this->errors);
    }
    /**
     * Format field operator name
     * @param type $field
     * @return type
     */
    public function getFieldOpName($field)
    {
        return $field.'_op';
    }
    /**
     * Retrieve the operator value
     * @param type $field
     * @return type
     */
    public function getFieldOpValue($field)
    {
        if (isset($this->ops[$field]))
            return $this->ops[$field];
        else
            return null;
    }
    /**
     * Check if operator exists
     * @param type $field
     * @return type
     */    
    public function parseFieldOpValue($field)
    {
        //Note: longer length operator will be scanned first
        //attempt to search op set (3 chars length)
        $op = substr($this->$field,0,3);
        if (in_array($op,[self::OP_LAST_30_DAYS,self::OP_LAST_90_DAYS]))
            return $op;
        //attempt to search op set (2 chars length)
        $op = substr($this->$field,0,2);
        if (in_array($op,[self::OP_LAST_24_HOURS,self::OP_LAST_7_DAYS,self::OP_GREATER_THAN_OR_EQUAL,self::OP_LESS_THAN_OR_EQUAL,self::OP_NOT_EQUAL]))
            return $op;
        //attempt to search first op set (1 char length)
        $op = substr($this->$field,0,1);
        if (in_array($op,[self::OP_CONTAINS,self::OP_EQUAL,self::OP_GREATER_THAN,self::OP_LESS_THAN]))
            return $op;
        
        return null;
    }
    
    public function parseHtmlOptions($config,$value)
    {
        $htmlOptions = isset($config['htmlOptions'])?$config['htmlOptions']:[];
        if (!empty($value)){
            if (isset($htmlOptions['class']))
                $htmlOptions['class'] .= ' filled';
            else
                $htmlOptions['class'] = 'filled';
        }
        return $htmlOptions;
    }
    /**
     * Need this to make the css class read from widget and not hardcoded
     */
    public function getWidgetCssClass()
    {
        $widget = new SPageFilter();
        return $widget->cssClass;
    }
    /**
     * Verify object status
     */
    public function ruleStatus($attribute,$params)
    {
        if (!empty($this->$attribute)){
            if (!in_array($this->$attribute,array_keys(self::getAllStatus())))
                $this->addError($attribute,Sii::t('sii','Invalid status.'));
        }
    }     
    /**
     * The supported status list
     * Its value is set to be the column "text" of model Process
     * @see Process::text
     * 
     * @return array field status
     */
    public static function getAllStatus($status=[])
    {
        return self::_getData([ 
                self::STATUS_ONLINE=>Process::getDisplayText(self::STATUS_ONLINE),
                self::STATUS_OFFLINE=>Process::getDisplayText(self::STATUS_OFFLINE),
                self::STATUS_EXPIRED=>Process::getDisplayText(self::STATUS_EXPIRED),
                self::STATUS_APPROVED=>Process::getDisplayText(self::STATUS_APPROVED),
                self::STATUS_REJECTED=>Process::getDisplayText(self::STATUS_REJECTED),
                self::STATUS_PENDING=>Process::getDisplayText(self::STATUS_PENDING),
            ], $status);
    }       
    /**
     * NOTE: The order of operators are important
     * @return array field operators
     */
    public static function getOperators($ops=[])
    {
        return self::_getData([ //Sorted by string length DESC
            self::OP_LAST_90_DAYS=>Sii::t('sii','Last 90 days'),
            self::OP_LAST_30_DAYS=>Sii::t('sii','Last 30 days'),
            self::OP_LAST_24_HOURS=>Sii::t('sii','Last 24 hours'),
            self::OP_LAST_7_DAYS=>Sii::t('sii','Last 7 days'),
            self::OP_GREATER_THAN_OR_EQUAL=>Sii::t('sii','Is greater than or equal to'),
            self::OP_LESS_THAN_OR_EQUAL=>Sii::t('sii','Is less than or equal to'),
            self::OP_NOT_EQUAL=>Sii::t('sii','Is not equal to'),
            self::OP_CONTAINS=>Sii::t('sii','Contains'),
            self::OP_EQUAL=>Sii::t('sii','Is equal to'),
            self::OP_GREATER_THAN=>Sii::t('sii','Is greater than'),
            self::OP_LESS_THAN=>Sii::t('sii','Is less than'),
        ], $ops);
    }       
    /**
     * A boilerplate method to construct array data
     */
    private static function _getData($dataSource,$data=[])
    {
        if (empty($data)){
            return $dataSource;
        }
        else {
            $result = [];
            foreach ($data as $d) {
                if (isset($dataSource[$d]))
                    $result[$d] = $dataSource[$d];
            }
            return $result;
        }
        
    }
    
}
