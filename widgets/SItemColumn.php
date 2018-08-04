<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * ItemColumn class file.
 *
 * This column assumes that the input data is coming from <model>::getItemColumnData()
 * 
 * Not support sorting
 * 
 * Example Usage:
 * <pre>
 *  Yii::import('common.components.widgets.SItemColumn');
 *  $this->widget('zii.widgets.grid.CGridView', array(
 *	'id'=>'order-grid',
 *	'dataProvider'=>$model->search(),
 *	'filter'=>$model,
 *	'columns'=>array(
 *		'id',
 *		array(
 *                  'class' =>'SItemColumn',
 *                    'label' => 'Item Name',
 *                    'value' => '$data->getItemColumnData()',
 *                ),
 *		'total_price',
 *		'grand_total',
 *		array(
 *			'class'=>'CButtonColumn',
 *		),
 *	),
 *  )); ?>
 * </pre>
 * @author kwlok
 */
class SItemColumn extends CDataColumn
{
    public $label;
    public $value;
    public function init()
    {
        parent::init();
    }

    protected function renderHeaderCellContent()
    {
        if ($this->label!=null)
            echo CHtml::encode($this->label);
        else
            parent::renderHeaderCellContent();
    }

    protected function renderDataCellContent($row,$data)
    {
        if($this->value!==null) {
            $value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
            $html = '<table class="imagelist">';
            foreach ($value as $name=>$field ){
                $param = (object)$field;
                $html .= '<tr class="'.($row%2?'even':'odd').'">';
                if (isset($param->image)){
                    $image = (object)$param->image;
                    if (isset($image->type) && isset($image->external) && $image->external){
                        $html .= '<td width="30px" style="vertical-align:top">'.CHtml::image($image->externalImageUrl,Sii::t('sii','Image'),array_merge(isset($image->htmlOptions)?$image->htmlOptions:array('style'=>'padding-right:10px'),array('width'=>$image->version.'px'))).'</td>';
                    }
                    elseif (isset($image->type) && isset($image->imagePath)){
                        $type = $image->type;
                        if ($type=='Image')
                           Yii::app()->image->imagePath = $image->imagePath;
                        $htmlOptions = isset($image->htmlOptions)?$image->htmlOptions:array('style'=>'padding-right:10px');
                        $img = $type::model()->findbyPk($image->id);
                        if ($img!=null)
                            $thumbnail = $img->render($image->version,Sii::t('sii','Image Alt'),$htmlOptions);
                        else {
                            $_original = Yii::app()->image->modelClass;
                            Yii::app()->image->modelClass = 'Image';
                            $thumbnail = Yii::app()->image->loadModel($image->default)->render($image->version,Sii::t('sii','Image Alt'),$htmlOptions);
                            Yii::app()->image->modelClass = $_original;//restore back original
                        }
                        $html .= '<td width="30px" style="vertical-align:top">'.$thumbnail.'</td>';
                    }
                    else      
                        $html .= '<td width="30px">'.Chtml::image(Yii::app()->image->getUrl($image->id,$image->version),'',array('style'=>'padding-right:10px')).'</td>';
                }
                $html .= '<td style="vertical-align:top">';

                if (isset($param->quantity))
                    $html .= $param->quantity.' x ';

                $html .= '<span class="itemname" style="margin-right:5px;">'.$name.'</span>';

                if (isset($param->status)){
                    $status = (object)$param->status;
                    $html .= '<span class="tag small rounded2" style="background:'.$status->color.'">'.Sii::t('sii','{text}',array('{text}'=>$status->text)).'</span>';
                }

                if (isset($param->tracking)){
                     $tracking = (object)$param->tracking;
                     if (isset($tracking->num))
                        $html .= ' <span id="tracking" class="tag small rounded2">'.l($tracking->num,$tracking->url,array('target'=>'_blank')).'</span>';            
                }

                $keyValuePairs = array();
                if (isset($param->campaign_tag))
                    $keyValuePairs = array_merge($keyValuePairs,$param->campaign_tag);

                if (isset($param->sku))
                    $keyValuePairs = array_merge($keyValuePairs,$param->sku);

                if (isset($param->weight))
                    $keyValuePairs = array_merge($keyValuePairs,$param->weight);

                if (isset($param->options))
                    $keyValuePairs = array_merge($keyValuePairs,$param->options);

                if (!empty($keyValuePairs))
                    $html .= Helper::htmlSmartKeyValues($keyValuePairs);

                if (isset($param->list_objects)){
                    $html .= Helper::htmlList($param->list_objects,array('class'=>'list-objects','style'=>'margin:0;padding:0;'));
                }

                $html .= '</td></tr>';
            }
            $html .= '</table>';
            echo $html;
        }
        else
            echo '';

    }
}
