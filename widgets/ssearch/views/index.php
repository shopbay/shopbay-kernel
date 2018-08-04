<div class="ssearch">
    <ul>
        <li class="search-text">
            <?php echo CHtml::textField($this->id,'',['maxlength'=>200,'placeholder'=>$this->placeholder]);?>
        </li>
        <li class="search-icon">
            <?php echo CHtml::link('<i class="fa fa-search"></i>','javascript:void(0);',['class'=>'search-button','onclick'=>$this->searchScript]);?>
        </li>
    </ul>
</div> 
<?php 
cs()->registerScript(__CLASS__.$this->id,'$(\'#'.$this->id.'\').bind("enterKey",function(e){'.$this->searchScript.';});$(\'#'.$this->id.'\').keyup(function(e){if(e.keyCode == 13) $(this).trigger("enterKey");});',CClientScript::POS_END);