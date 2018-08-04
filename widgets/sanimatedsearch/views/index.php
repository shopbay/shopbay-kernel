<div class="sanimatedsearch">
    <form action="" autocomplete="on">
        <input id="<?php echo $this->inputId;?>" name="<?php echo $this->inputId;?>" type="text" placeholder="<?php echo $this->placeholder;?>">
        <?php if ($this->useImage):?>
            <input id="search_submit" value="" type="submit">
        <?php else:?>
            <i id="search_submit" class="fa fa-search"></i>
        <?php endif;?>
    </form>    
</div>
<?php 
cs()->registerScript(__CLASS__.$this->inputId,'$(\'#'.$this->inputId.'\').bind("enterKey",function(e){'.$this->searchScript.';});$(\'#'.$this->inputId.'\').keyup(function(e){if(e.keyCode == 13) $(this).trigger("enterKey");});',CClientScript::POS_END);