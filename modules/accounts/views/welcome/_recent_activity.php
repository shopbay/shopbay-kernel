<div class="recent-wrapper">
    <h2><?php echo Sii::t('sii','Recent Activity');?></h2>
    <?php $this->widget($this->getModule()->getClass('listview'), array(
                'id'=>'activity',
                'dataProvider'=>$activity,
                'template' => '{items}',  
                'viewData' => array('trimLength'=>35),  
                'itemView'=>$this->getModule()->getView('activity'),
            ));
    ?>
</div>


