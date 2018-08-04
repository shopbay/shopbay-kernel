<?php echo Sii::t('sii','{app} is the best place to shop and buy cool stuff that you want and love, and it’s great to have you on board!',array('{app}'=>param('SITE_NAME')));?>

<p>
    <?php echo Sii::t('sii','To make your life easier, we thought it might be helpful to point you to some awesome ways to use {app}.',array('{app}'=>param('SITE_NAME')));?>
</p>

<div style="border-top: 1px solid lightgray;border-bottom: 1px solid lightgray;padding-bottom: 20px;">

    <h3><?php echo Sii::t('sii','Explore what is Trending on {app}',array('{app}'=>param('SITE_NAME')));?></h3>
    <p>
        <?php echo Sii::t('sii','You can discover, share, buy wonderful stuff that you want and love all here in a wide variety of characteristic and fun marketplaces we provide.');?>
    </p>
    <a href="<?php echo Yii::app()->urlManager->createHostUrl();?>" style="font-size: 1.2em;display: inline-block;background: lightgreen;color: white;padding: 10px 20px;">
        <?php echo Sii::t('sii','Shop Now');?>
    </a>    
    <h3><?php echo Sii::t('sii','Be a Seller');?></h3>
    <p>
        <?php echo Sii::t('sii','If you want to be a seller, it takes only few minutes to sign yourself up as a {app} merchant.',array('{app}'=>param('SITE_NAME')));?>
    </p>
    <a href="<?php echo Yii::app()->urlManager->createHostUrl('/signin');?>" style="font-size: 1.2em;display: inline-block;background: darksalmon;color: white;padding: 10px 20px;">
        <?php echo Sii::t('sii','Start Business');?>
    </a>    

</div>

<p>
    <?php echo Sii::t('sii','We have also sent you a verification email in your inbox - please follow the link inside it so we can confirm your email address is really yours.');?>
</p>

<p>
    <?php echo Sii::t('sii','Thanks so much for your time — we’re looking forward to seeing you enjoy shopping in {app}!',array('{app}'=>param('SITE_NAME')));?>
</p>