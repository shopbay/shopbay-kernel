<div class="question-group">

    <div class="name">
        <?php echo Sii::t('sii','Questions');?>
    </div>
    <?php echo l(SPageMenu::menuItem('ask',Sii::t('sii','ask'),Sii::t('sii','Ask Question')),
                 $this->getQuestionAskUrl(),
                 array('style'=>'text-decoration:none')); 
    ?>

</div>
