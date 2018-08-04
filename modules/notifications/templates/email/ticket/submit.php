<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:30px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                    <span style="font-size:2em;margin:10px"><?php echo $model->subject;?></span>
                    <div style="float:right;">
                        <?php if ($model->shop_id!=null):?>
                        <div>
                            <strong><?php echo Sii::t('sii','Shop');?></strong>
                            <span><?php echo $model->shop->displayLanguageValue('name',user()->getLocale());?></span>
                        </div>
                        <?php endif;?>
                        <div>
                            <strong><?php echo Sii::t('sii','Submission Date');?></strong>
                            <span><?php echo $model->formatDateTime($model->create_time,true);?></span>
                        </div>
                        <div>
                            <strong><?php echo Sii::t('sii','Submitted By');?></strong>
                            <span>
                                <?php echo $model->account->name;?>  
                                <?php echo $model->account->getAvatar(Image::VERSION_XXSMALL); ?> 
                            </span>
                        </div>
                    </div>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p><?php echo Sii::t('sii','Dear Administrator,');?></p>

                    <div style="margin-top:10px;">
                        
                        <p>
                            <?php echo Sii::t('sii','You have a new ticket.');?>
                        </p>
                        
                        <p>
                            <?php echo Helper::purify($model->content);?>
                        </p>  
                        
                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>