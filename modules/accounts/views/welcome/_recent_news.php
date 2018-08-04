<div class="recent-wrapper">
    <h2><?php echo Sii::t('sii','Latest News {link}',array('{link}'=>CHtml::link(Sii::t('sii','More'),url('news'),array('class'=>'more-news'))));?></h2>
    <?php $this->widget($this->getModule()->getClass('listview'), array(
                'id'=>'news',
                'dataProvider'=>$news,
                'template' => '{items}',  
                'itemView'=>$this->getModule()->getView('customernews'),
            ));
    ?>
</div>
