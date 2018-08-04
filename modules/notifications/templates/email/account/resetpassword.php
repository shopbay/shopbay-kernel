<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Password Reset');?></span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::t('sii','Dear {customer},',array('{customer}'=>$name));?></p>

                    <div style="margin-top:10px;">
                        
                        <p><?php echo Sii::t('sii','You receive this email because you have recently claimed that you have forgotten your password and request to reset it.');?></p>

                        <p><?php echo Sii::t('sii','Your temporary password is "<b>{password}</b>"',array('{password}'=>$password));?></p>

                        <p><?php echo Sii::t('sii','For security reason, we advise you to sign in your account immediately and change it to your own password.');?></p>

                        <p><?php echo Sii::t('sii','Click here to {login}.',['{login}'=>CHtml::link(Sii::t('sii','sign in'),url('signin'))]);?></p>
                        
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
