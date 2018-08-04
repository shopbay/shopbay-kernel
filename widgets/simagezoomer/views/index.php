<?php $this->widget($this->zoomer,[
    'config'=>request()->isMobile()?$this->mobileConfig:$this->config,
    'images'=>$this->images,
    'smallVersion'=>$this->defaultVersion,
    'thumbVersion'=>$this->thumbnailVersion,
]);
