<p>
    <a href="<?php echo $this->disableLink==true?'#':Yii::app()->createUrl($this->route . '/oauth', array('provider' => $provider)); ?>" class="zocial <?= ($this->iconOnly ?'icon':'').' '.strtolower($provider) ?>">
        <?= $this->getButtonText($provider,!$this->disableLink); ?>
    </a>
</p>
