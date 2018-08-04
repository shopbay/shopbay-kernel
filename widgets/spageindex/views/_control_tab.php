<ul class="tabs">
    <?php foreach ($this->filters as $filter): ?>
    <li id="tab-<?php echo $filter;?>">
        <a href="javascript:void(0);" <?php echo $this->scope==strtolower($filter)?'class="active"':''?> 
           onclick="<?php echo $this->getControlOnclick($filter);?>">
            <?php echo $filter;?>
        </a>
    </li>
    <?php endforeach;?>
</ul>

