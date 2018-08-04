<?php if ($this->showRecentMessages())
          $this->renderPartial('_recent_messages',array('messages'=>$this->getRecentMessages()));
?>
<?php if ($this->showRecentNews())
          $this->renderPartial('_recent_news',array('news'=>$this->getRecentNews()));
?>
<br>
<?php if ($this->showRecentActivities())
          $this->renderPartial('_recent_activity',array('activity'=>$this->getRecentActivity()));
