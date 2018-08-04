<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Account Closure.');?></span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::t('sii','Dear {user},',array('{user}'=>$name));?></p>

                    <div style="margin-top:10px;">
                        
                        <div style="margin-top:10px;">

                            <p><?php echo Sii::t('sii','We are regretted losing you but we respect your decision. Your account is now closed.');?></p>

                            <p><?php echo Sii::t('sii','If you ever change mind later, you are always welcome to sign up with us again.');?></p>

                        </div>

                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>
