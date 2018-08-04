<div class="tasks">
    <?php 
        $this->renderView($this->tasksView,array('role'=>$role));

        if ($this->showAskQuestion())
            $this->renderPartial('_questions');
    ?>
</div>
