<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Shop application details:');?>
</p>
<table style="width:300px;">
    <tr>
        <td><?php echo Sii::t('sii','Shop Name');?></td>
        <td><?php echo $model->displayLanguageValue('name',$model->getLocale());?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Request Date');?></td>
        <td><?php echo $model->formatDateTime($model->create_time,true);?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Request Person');?></td>
        <td><?php echo $model->account->getAvatar(Image::VERSION_XXSMALL); ?> 
            <span><?php echo $model->account->name;?></span>    
        </td>
    </tr>
</table>