<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;margin:10px"><?php echo Sii::t('sii','Welcome to {app}!',['{app}'=>param('SITE_NAME')]);?></span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 25px;padding-left:20px;">
                    <?php $this->renderPartial('common.modules.notifications.templates.message.account.welcome',[
                            'model'=>$model,
                            'merchantRole'=>isset($merchantRole)?true:null,
                        ]);
                    ?>
                </td>
            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>
