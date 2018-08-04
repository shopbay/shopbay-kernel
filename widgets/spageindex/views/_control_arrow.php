<ul class="arrows <?php echo $this->locale;?> ">
  <?php foreach ($this->filters as $filter => $filterDisplay): ?>
  <li id="arrow-<?php echo $filter;?>" <?php echo $filter==$this->scope?'class="active"':''?> >
    <a href="javascript:void(0);" onclick="<?php echo $this->getControlOnclick($filter);?>">
        <?php echo $filterDisplay;?>
    </a>
    <div class="arrow-wrapper <?php echo $filter;?> <?php echo $filter==$this->scope?'show':'hidden'?>">
        <div class="arrow"></div>
        <div class="arrow-shadow"></div>
    </div>
  </li>
<?php endforeach;?>
</ul>
<?php Yii::app()->clientScript->registerScript('arrow-script', "renderarrow('".$this->scope."');");?>