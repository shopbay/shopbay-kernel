<div class="search-bar">
    <ul>
        <li class="search-text">
            <?php echo CHtml::textField(isset($input)?$input:'q',isset($value)?$value:'',array('maxlength'=>200,'placeholder'=>$placeholder));?>
        </li>
        <li>
            <?php echo CHtml::link('<i class="fa fa-search"></i>','javascript:void(0);',array('class'=>'search-button','onclick'=>(isset($onsearch)?$onsearch:'dosearch()')));?>
        </li>
    </ul>
</div> 
<?php 
cs()->registerScript($onsearch,'$(\'#'.(isset($input)?$input:'q').'\').bind("enterKey",function(e){'.(isset($onsearch)?$onsearch:'dosearch()').';});$(\'#'.(isset($input)?$input:'q').'\').keyup(function(e){if(e.keyCode == 13) $(this).trigger("enterKey");});',CClientScript::POS_END);