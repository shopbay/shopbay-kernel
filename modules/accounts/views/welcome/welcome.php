<?php if (user()->isActivated):?>
<div class="welcome-block rounded width-p-38">
    <h3 class="message">
        <?= Sii::t('sii','First, you need to setup your first shop.');?>    
    </h3>
    <?php $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'shop-button',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Enter My First Shop'),
                    'value'=>'shopBtn',
                    'onclick'=>'js:function(){window.location.href="'.url('shop/start').'";}',
                ]); 
    ?>    
</div>
<?php endif;?>
<div class="rounded width-p-50" style="display:inline-block;margin-left: 20px;">
    <?php echo $this->merchantWizard->renderAdvices(); ?>
</div>
