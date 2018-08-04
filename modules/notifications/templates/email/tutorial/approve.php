<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:30px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                    <span style="font-size:2em;margin:10px;vertical-align: middle"><?php echo Sii::t('sii','Tutorial');?></span>
                    <div style="float:right;">
                        <div>
                            <strong><?php echo Sii::t('sii','Submission Date');?></strong>
                            <span><?php echo $model->formatDateTime($model->create_time,true);?></span>
                        </div>
                        <div>
                            <strong><?php echo Sii::t('sii','Submitted By');?></strong>
                            <span>
                                <?php echo $model->author->name;?>  
                                <?php echo $model->author->getAvatar(Image::VERSION_XXSMALL); ?> 
                            </span>
                        </div>
                    </div>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::t('sii','Dear user,');?></p>

                    <div style="margin-top:10px;">
                        
                        <p style="font-size:1.1em">
                            <?php echo Sii::t('sii','Your tutorial has been approved and is now published to community portal');?>
                        </p>
                        
                        <p style="font-size:1.1em">
                            <?php echo CHtml::link(Sii::t('sii','Click here to view tutorial online.'),$model->url);?>
                        </p>
                        
                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>