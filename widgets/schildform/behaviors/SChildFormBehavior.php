<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SChildFormBehavior
 *
 * @author kwlok
 */
class SChildFormBehavior extends CBehavior 
{
    /**
     * @var boolean If to show delete button
     */
    public $deleteButton = true;    
    /**
     * @var string The name of the delete script function name
     */
    public $deleteScriptName;
    /**
     * @var array The name of the hidden attributes
     */
    public $hiddenAttributes = array();
    /**
     * @var array The name of the non locale attributes
     */
    public $nonLocaleAttributes = array();

    public function renderHiddenFields()
    {
        $form = $this->getOwner();
        $output = CHtml::activeHiddenField($form,$form->keyAttribute,array('name'=>$form->formatAttributeName(null,$form->keyAttribute)));
        foreach ($this->hiddenAttributes as $attribute) {
            $output .= CHtml::activeHiddenField($form,$attribute,array('name'=>$form->formatAttributeName(null,$attribute)));
        }
        return $output;
    }
    
    public function renderNonLocaleAttributes()
    {
        $form = $this->getOwner();
        $output = '';
        foreach ($this->nonLocaleAttributes as $attribute => $config) {
            $output .= CHtml::openTag('td',array('style'=>'text-align:center','class'=>'nonlocale-attribute'));
            if (isset($config['htmlField']) && !$config['htmlField']){
                if (isset($config['rawValueCallback'])){
                    $output .= $form->{$config['rawValueCallback']}();
                }
            }
            else {
                if (isset($config['textPrefix']))
                    $output .= CHtml::tag('span',array('class'=>'text-prefix'),$config['textPrefix']);
                if (isset($config['textPrefixCallback']))
                    $output .= CHtml::tag('span',array('class'=>'text-prefix'),$form->{$config['textPrefixCallback']}());

                $output .= CHtml::activeTextField($form,$attribute,array('name'=>$form->formatAttributeName(null,$attribute),'size'=>$config['size'],'maxlength'=>$config['maxlength'],'class'=>$form->hasErrors($form->formatErrorName(null,$attribute))?'error':'')); 
            
                if (isset($config['textSuffix']))
                    $output .= CHtml::tag('span',array('class'=>'text-suffix'),$config['textSuffix']);
                $output .= CHtml::error($form,$form->formatErrorName(null,$attribute));
            }
            $output .= CHtml::closeTag('td');
        }
        return $output;
    }    

    public function hasNonLocaleAttributes()
    {
        return isset($this->nonLocaleAttributes) && is_array($this->nonLocaleAttributes);
    }       
    
    public function getDeleteOnclick()
    {
        return 'javascript:'.$this->deleteScriptName.'('.$this->getOwner()->id.');';
    }

    public function showDeleteButton()
    {
        return $this->deleteButton;
    }

}