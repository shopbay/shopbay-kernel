<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Your ticket has a response.');?>
</p>
<table style="width:100%">
    <?php if ($model->shop_id!=null):?>
    <tr>
        <td><strong><?php echo Sii::t('sii','Shop');?></strong></td>
        <td><?php echo $model->shop->displayLanguageValue('name',user()->getLocale());?></td>
    </tr>
    <?php endif;?>
    <tr>
        <td><strong><?php echo Sii::t('sii','Reply Date');?></strong></td>
        <td><?php echo $model->formatDateTime($model->create_time,true);?></td>
    </tr>
    <tr>
        <td><strong><?php echo Sii::t('sii','Reply By');?></strong></td>
        <td><?php echo $model->account->getAvatar(Image::VERSION_XXSMALL); ?> 
            <span><?php echo $model->account->name;?></span>    
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p><?php echo Helper::purify($model->content);?></p>    
        </td>
    </tr>    
</table>