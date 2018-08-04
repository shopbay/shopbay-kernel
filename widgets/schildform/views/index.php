<div class="grid-view" <?php echo $this->htmlOptionsToString();?> >
    <?php if (isset($this->divSection)):?>
    <div class="div-section">
        <?php echo $this->divSection;?>
    </div>
    <?php endif;?>
    <table id="child_table" <?php echo $this->hasData?'class="items"':''; ?> data-delete-control="<?php echo $this->deleteControl;?>">
        <thead <?php echo $this->hasData?'':'style="display:none"'; ?> >
            <tr>
                <?php echo $this->getHeader();?>
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach ($this->loadData() as $form){
                    $this->render($this->formView,array('form'=>$form));
                }
            ?>
        </tbody>                
    </table>
</div>
<?php 
$this->registerRunScript();