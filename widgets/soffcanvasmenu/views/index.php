<?php foreach($this->menus as $menu):?>
   <?php $this->render('_menu',['menu'=>$menu,'pageId'=>$this->pageId]);?>
<?php endforeach;?>

<div class="canvas-page">
    
    <?php if ($this->autoMenuOpeners): ?>
    <div class="canvas-menu">
        <?php echo $this->renderMenuOpeners();?>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->pageContent)): ?>
    <div id="<?php echo $this->pageId;?>" class="canvas-page-content">
        <?php echo $this->pageContent; ?>
    </div>
    <?php endif; ?>
</div>
