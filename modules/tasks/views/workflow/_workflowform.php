<?php if ($model->actionable(user()->currentRole,user()->getId())):?>
    <div class="task-form">
        <?php $this->_getTransitionForm($model,$action,$decision);?>
        <div id="attachment">
            <?php $this->_getAttachmentForm($model,$action,$decision);?>
        </div>
    </div>
<?php endif;?>
