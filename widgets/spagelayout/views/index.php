<div id="page_layout_<?php echo $this->id;?>" class="page-layout" style="width:<?php echo isset($this->width)?$this->width.'px':'auto';?>;">
    <?php 
        if (isset($this->columns[SPageLayout::COLUMN_MAIN]))
            echo CHtml::tag('div', $this->getColumnHtmlOptions(SPageLayout::COLUMN_MAIN), $this->columns[SPageLayout::COLUMN_MAIN]);
        if (isset($this->columns[SPageLayout::COLUMN_LEFT]))
            echo CHtml::tag('div', $this->getColumnHtmlOptions(SPageLayout::COLUMN_LEFT), $this->columns[SPageLayout::COLUMN_LEFT]);
        if (isset($this->columns[SPageLayout::COLUMN_CENTER]))
            echo CHtml::tag('div', $this->getColumnHtmlOptions(SPageLayout::COLUMN_CENTER), $this->columns[SPageLayout::COLUMN_CENTER]);
        if (isset($this->columns[SPageLayout::COLUMN_RIGHT]))
            echo CHtml::tag('div', $this->getColumnHtmlOptions(SPageLayout::COLUMN_RIGHT), $this->columns[SPageLayout::COLUMN_RIGHT]);
    ?>
</div>