<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Account Email Reset');?></span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::t('sii','Dear {customer},',array('{customer}'=>$name));?></p>

                    <div style="margin-top:10px;">
                        
                        <p><?php echo Sii::t('sii','You received this email because you have recently changed your email address for your account at {app}.',array('{app}'=>app()->name));?></p>

                    </div>
                    <div style="margin-top:10px;">
                        
                        <p><?php echo Sii::t('sii','Please use the following link to re-activate your account');?></p>

                        <a href="<?php echo Account::model()->getActivationUrl($activate_str);?>" style="display:block;color:white;text-align:center;width:100%;background:lightskyblue;font-size:3em;margin: 30px 0px;">
                            <?php echo Sii::t('sii','Activate Now');?>
                        </a>                  
                        
                    </div>
                    <div style="margin-top:10px;">
                        
                        <p><?php echo Sii::t('sii','If you are not the one who initiated this request, please let us know so that we can immediately look into the issue.');?></p>

                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>