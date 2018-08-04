<?php echo Sii::t('sii','{app} is the best place to build shops and ship products for your business, and it’s great to have you on board!',array('{app}'=>param('SITE_NAME')));?>

<p>
    <?php echo Sii::t('sii','To make your life easier, we thought it might be helpful to point you to some awesome ways to use {app}.',array('{app}'=>param('SITE_NAME')));?>
</p>

<div style="border-top: 1px solid lightgray;border-bottom: 1px solid lightgray;padding-bottom: 20px;">

    <h3><?php echo Sii::t('sii','Build Shop');?></h3>
    <p>
        <?php echo Sii::t('sii','Jumpstart to create your first shop to start your business. It has all the features you need to run an ecommerce business for your products.');?>
    </p>
    <a href="<?php echo Yii::app()->urlManager->createHostUrl('/signin');?>" style="font-size: 1.2em;display: inline-block;background: lightgreen;color: white;padding: 10px 20px;">
        <?php echo Sii::t('sii','Start Business');?>
    </a>    

    <h3><?php echo Sii::t('sii','Help and Support');?></h3>
    <p>
        <?php echo Sii::t('sii','Check out help at our community portal to learn about {app} superpowers. Learn to create tutorials, ask questions, share knowledge, and more.',array('{app}'=>param('SITE_NAME'),'{community}'=>CHtml::link(Sii::t('sii','Community Portal'),Yii::app()->urlManager->createCommunityUrl())));?>
    </p>        
    <a href="<?php echo Yii::app()->urlManager->createCommunityUrl();?>" style="font-size: 1.2em;display: inline-block;background: skyblue;color: white;padding: 10px 20px;">
        <?php echo Sii::t('sii','Join Community');?>
    </a>    

</div>

<p>
    <?php echo Sii::t('sii','We have also sent you a verification email in your inbox - please follow the link inside it so we can confirm your email address is really yours.');?>
</p>

<p>
    <?php echo Sii::t('sii','Thanks so much for your time — we’re looking forward to seeing what you build with {app} and have prosperous business!',array('{app}'=>param('SITE_NAME')));?>
</p>

    