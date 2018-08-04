<div class="name-wrapper <?php echo isset($this->heading['tag'])?'with-tag':'';?>" >
    <?php if (!empty($this->heading['image'])): ?>
    <span class="image">
        <?php echo $this->heading['image'];?>
    </span> 
    <?php endif; ?>

    <div class="name">

        <?php if (isset($this->heading['superscript'])): ?>
        <div class="superscript"><?php echo $this->heading['superscript'];?></div>   
        <?php endif; ?>

        <?php echo $this->heading['name'];?>

        <?php if (isset($this->heading['subscript'])): ?>
        <div class="subscript"><?php echo $this->heading['subscript'];?></div>   
        <?php endif; ?>

    </div>
</div>

<?php if (isset($this->heading['tag'])): ?>
<div class="tag-wrapper" style="">

    <span class="tag" <?php echo $this->getHeadingTagStyle();?>><?php echo $this->getHeadingTag();?></span>

</div>
<?php endif; ?>

