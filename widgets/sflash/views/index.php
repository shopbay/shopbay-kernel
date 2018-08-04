<?php 
if (is_array($this->key)){
    foreach ($this->key as $index => $key) {
       if (Yii::app()->user->hasFlash($key))
           $this->render('_flash',array('id'=>'flash_'.$index,'flash'=>(object)Yii::app()->user->getFlash($key,Sii::t('sii','Not set'))));
    }
}
else {
   if (Yii::app()->user->hasFlash($this->key))
       $this->render('_flash',array('flash'=>(object)Yii::app()->user->getFlash($this->key,Sii::t('sii','Not set'))));
}
