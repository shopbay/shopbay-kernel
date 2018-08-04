<?php $this->registerListViewCssFile();?>

<?php if (isset($config['widget'])):?>

    <?php echo $config['widget'];?>

<?php else: ?>

    <div class="list-box" style="position: relative">
        <p>
            <?php echo Sii::t('sii','This is the plain <span style="color:red">{scope}</span> list view for model <span style="color:green">{model}</span>.',array('{scope}'=>strtoupper($config['scope']),'{model}'=>ucfirst($config['model'])));?>
        </p>
        <p>
            <?php echo Sii::t('sii','Please custom your own view.');?>
        </p>
    </div>

<?php endif;?>

