<?php $this->registerGridViewCssFile();?>
<?php if (isset($config['widget'])):?>

    <?php echo $config['widget'];?>

<?php else: ?>

    <div class="grid-view">
        <table class="items">
            <thead>
                <tr>
                    <th><?php echo Sii::t('sii','Scope');?></th>
                    <th><?php echo Sii::t('sii','Model');?></th>
                    <th><?php echo Sii::t('sii','Remarks');?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="even">
                    <td style="text-align: center">
                        <span style="color:red"><?php echo strtoupper($config['scope']);?></span>
                    </td>
                    <td style="text-align: center">
                        <span style="color:green"><?php echo ucfirst($config['model']);?></span>
                    </td>
                    <td style="text-align: center">
                        <?php echo Sii::t('sii','This is the plain grid view. Please custom your own view.');?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

<?php endif;?>
