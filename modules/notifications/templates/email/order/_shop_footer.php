<div style="padding: 0px 15px;font-size: 0.8em">
    <?php echo Sii::t('sii','Note: Please do not reply to this email; For any support issue, please contact us at <em>{email}</em>',['{email}'=>$shop->email]);?>
</div>
<div style="border:0;padding: 20px 15px;font-size: 0.8em">
    <?php if ($shop->getCustomDomain()!=null):?>
        <?php echo Sii::t('sii','{shop}',['{shop}'=>$shop->displayLanguageValue('name',user()->getLocale())]);?>
        <ul style="list-style-type: none;display: inline;padding-left: 10px;">
            <li style="display:inherit;margin: 0px;"><?php echo CHtml::link(ShopPage::getTitle(ShopPage::TOS),ShopPage::getPageUrl($shop, ShopPage::TOS));?></li>
            <li style="display:inherit;margin: 0px;"><?php echo CHtml::link(ShopPage::getTitle(ShopPage::PRIVACY),ShopPage::getPageUrl($shop, ShopPage::PRIVACY));?></li>
        </ul>
    <?php else: ?>
        <?php echo Sii::t('sii','{shop} - Powered by {app} &copy; 2015 - {year}.',['{shop}'=>$shop->displayLanguageValue('name',user()->getLocale()),'{year}'=>date('Y'),'{app}'=>param('ORG_NAME')]);?>
    <?php endif;?>
</div>
