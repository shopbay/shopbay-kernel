<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Your tutorial submission is not approved. Please read the comments and make amendment.');?>
</p>

<p style="font-size:1.1em">
    <?php echo Sii::t('sii','You can resubmit tutorial for review again.');?>
</p>
                        
<table style="width:100%">
    <tr>
        <td><strong><?php echo Sii::t('sii','Title');?></strong></td>
        <td><?php echo $model->localeName(user()->getLocale());?></td>
    </tr>
    <tr>
        <td><strong><?php echo Sii::t('sii','Submission Date');?></strong></td>
        <td><?php echo $model->formatDateTime($model->create_time,true);?></td>
    </tr>
    <tr>
        <td><strong><?php echo Sii::t('sii','Submitted By');?></strong></td>
        <td><?php echo $model->author->getAvatar(Image::VERSION_XXSMALL); ?> 
            <span><?php echo $model->author->name;?></span>    
        </td>
    </tr>
    <tr>
        <td><strong><?php echo Sii::t('sii','Difficulty');?></strong></td>
        <td><?php echo $model->getDifficultyText();?></td>
    </tr>
    <tr>
        <td><strong><?php echo Sii::t('sii','Tags');?></strong></td>
        <td><?php echo $model->tags;?></td>
    </tr>
</table>