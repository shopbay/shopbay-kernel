<div class="markdown">
    <?php 
        $this->beginWidget('CMarkdown', array('purifyOutput'=>false));
        echo file_get_contents($helpfile);
        $this->endWidget();
    ?>
</div>
