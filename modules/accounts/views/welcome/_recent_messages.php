<div class="recent-wrapper">
    <h2><?php echo Sii::t('sii','Latest Messages {link}',array('{link}'=>CHtml::link(Sii::t('sii','More'),url('messages'),array('class'=>'more-news'))));?></h2>
    <?php $this->widget($this->getModule()->getClass('listview'), array(
                'id'=>'messages',
                'dataProvider'=>$messages,
                'template' => '{items}',  
                'itemView'=>$this->getModule()->getView('customermessages'),
            ));
    ?>
</div>
