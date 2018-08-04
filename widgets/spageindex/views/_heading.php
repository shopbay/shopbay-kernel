<span class="view-name">
    <?php 
        if (isset($this->viewName)) 
            echo $this->viewName;
        else 
            echo SActiveRecord::plural($this->model);
    ?>
</span>

<?php if ($this->enableViewOptions): ?>
<div class="view-options">
    <ul>
        <?php foreach (SPageIndex::getViewOptions() as $key => $value) {
                echo '<li>'.CHtml::link($value==SPageIndex::VIEW_GRID?'<i class="fa fa-table"></i>':'<i class="fa fa-list-ul"></i>', 
                                        url($this->route,array('option'=>$value)),
                                        array('title'=>$value==SPageIndex::VIEW_GRID?Sii::t('sii','Grid View'):Sii::t('sii','List View'))).'</li>';
              }
        ?>
    </ul>
</div>
<?php endif;?>

<?php if (isset($this->control)): ?>
    <div class="view-control <?php echo $this->control==SPageIndex::CONTROL_TAB?'tab':'';?>">
        <?php $this->render($this->control);?>
    </div>

    <?php if ($this->control==SPageIndex::CONTROL_ARROW): ?>
        <div class="view-control-linebreak"></div>
    <?php endif;?>
<?php endif;?>
