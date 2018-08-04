<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('zii.widgets.grid.CGridColumn');
/**
 * EImageColumn class file.
 * EImageColumn represents a grid view column that displays an image, and optional, a link
 * This column assumes that the filename is saved as a path to the
 * image that is to be rendered. If no pathPrefix is given, it 
 * assumes Yii::app()->baseUrl as a prefix for the image.
 * 
 * Example Usage:
 * <pre>
 * 	Yii::import('application.components.EImageColumn');
 *   $this->widget('zii.widgets.grid.CGridView', array(
 * 	'id'=>'photo-grid',
 * 	'dataProvider'=>$model->search(),
 * 	'filter'=>$model,
 * 	'columns'=>array(
 * 		'id',
 * 		array(
 * 			'class'=>'EImageColumn',
 * 			'name' => 'filename',
 * 			'htmlOptions' => array('style' => 'width: 150px;'),
 * 			),
 * 		'album.title',
 * 		'album.category.title',
 * 		'title',
 * 		'filename',
 * 		array(
 * 			'class'=>'CButtonColumn',
 * 		),
 * 	),
 * )); ?>
 * </pre>
 * Bugfix: No need pathPrefix for true url; 
 *         Line 80 if (!$validator->validateValue($this->name))
 * Enhance: Support label for header cell 
 * 
 * @author kwlok
 * 
 */
class EImageColumn extends CGridColumn
{
    public $name;
    public $label;

    public $sortable;

    // Path to the image. 
    public $pathPrefix = null;
    public $pathSuffix = null;

    // wraps htmlOptions for the image/for the link
    public $htmlOptions = array();
    public $linkHtmlOptions = array();

    // alt attribute for the <image> tag
    public $alt = '';

    // optional: link
    public $link = false;

    public $filter = false;

    public function init()
    {
        parent::init();
        if($this->pathPrefix === null){
          $this->pathPrefix = Yii::app()->baseUrl . '/';

          $validator = new CUrlValidator();
          if (!$validator->validateValue($this->name))
               $this->pathPrefix = null;//valid url, no need pathPrefix
        }
        if($this->name===null)
            throw new CException(Sii::t('sii','Please specify a name for {class}.',array('{class}'=>get_class($this))));
    }

    protected function renderHeaderCellContent()
    {
        if($this->grid->enableSorting && $this->sortable && $this->name!==null)
            echo $this->grid->dataProvider->getSort()->link($this->name,$this->header);
        else if($this->name!==null && $this->header===null){
            if($this->grid->dataProvider instanceof CActiveDataProvider)
                echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
            else
                echo CHtml::encode($this->label);
        }
        else
            parent::renderHeaderCellContent();
    }

    protected function renderDataCellContent($row,$data)
    {
        $image = CHtml::image($this->pathPrefix . $data->{$this->name} . $this->pathSuffix,
                            $this->alt,
                            $this->htmlOptions);
        if($this->link)
                echo CHtml::link($image, $this->link, $this->linkHtmlOptions);
        else
                echo $image;
    }
}
