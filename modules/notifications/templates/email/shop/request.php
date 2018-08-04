<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Shop Application Notice');?></span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::t('sii','Dear Administrator,');?></p>

                    <div style="margin-top:10px;">
                        
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
                        
                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>