<div id="<?php echo $this->id;?>" class="page<?php echo ' '.$this->cssClass;?>" data-description="<?php echo $this->description?CHtml::encode($this->description):'';?>">

    <?php if (isset($this->flash)): ?>
    <div id="flash-bar" class="flash"><?php $this->widget('SFlash',array('key'=>$this->flash));?></div>
    <?php endif; ?>
    
    <?php if ($this->breadcrumbs): ?>
        <?php $this->widget('zii.widgets.CBreadcrumbs', array(
            'links'=>$this->breadcrumbs,
            'homeLink'=>$this->getBreadcrumbsHomeLinkIcon(),
            'separator'=>'<i class="fa fa-angle-right"></i>')); 
        ?>
    <?php endif; ?>

    <?php if (!empty($this->menu)): ?>
        <?php $this->widget('SPageMenu', array('items'=>$this->menu)); ?>
    <?php endif; ?>

    <div class="main-view">

        <?php if ($this->heading): ?>
            <div class="heading" id="heading">

                <?php   
                    if (is_array($this->heading)) 
                        echo $this->render('_heading');
                    else
                        echo $this->heading;
                ?>
                
                <?php if ($this->description): ?>
                <p class="description">
                    <?php echo $this->description; ?>
                </p>
                <?php endif; ?>
                
            </div>

            <?php if ($this->linebreak): ?>
                <div class="line-break"></div>    
            <?php endif; ?>

        <?php endif; ?>

        <div class="body">

            <?php echo $this->body;?>

            <?php if (!empty($this->sections)): ?>
            
                <?php if ($this->sectionLinebreak): ?>
                    <div class="line-break"></div>    
                <?php endif; ?>

                <?php $this->widget('SPageSection', array('sections'=>$this->sections));?>
                
            <?php endif;?>

        </div>    

    </div>
    
    <?php $this->renderLoader();?>

    <?php $this->renderCSRFToken();?>
    
</div>