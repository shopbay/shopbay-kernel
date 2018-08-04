<div id="<?php echo $this->container;?>" class="smodal-wrapper">
    <?php $this->widget('common.widgets.sloader.SLoader',array(
                'id'=>'smodal_loader',
                'type'=>SLoader::FULLSCREEN,
          ));
    ?>
    <div class="smodal-overlay" style="display:<?php echo $this->content==null?'none':'block';?>"></div>
    <div class="smodal-container" style="display:<?php echo $this->content==null?'none;':'block;';?>">
        <div class="smodal" style="<?php echo $this->getCssStyle();?>">
            <div class="smodal-meta"></div>
            <div class="smodal-content">
                <?php if (isset($this->content))
                    echo $this->content;
                ?>
            </div>
            <?php if ($this->closeButton):?>
            <div class="smodal-close">
               <?php echo l(Sii::t('sii','Close'),'javascript:void(0);',array('onclick'=>$this->getCloseScript(),'style'=>'color:white')); ?>
            </div>
            <?php endif;?>
        </div>
    </div>
</div>
