<?php
if (isset($this->topSection))
    $this->render('common.widgets.susermenu.views._section',['content'=>$this->topSection,'cssClass'=>'top']);

$this->render('common.widgets.susermenu.views._menu',['menu'=>isset($menu)?$menu:$this->getMenu($this->type)]);
    
if (isset($this->bottomSection))
    $this->render('common.widgets.susermenu.views._section',['content'=>$this->bottomSection,'cssClass'=>'bottom']);

//Mobile menu button
echo isset($mobileButton)?$mobileButton:null;  