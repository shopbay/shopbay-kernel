<?php
$this->widget('common.widgets.soffcanvasmenu.SOffCanvasMenu',[
    'menus'=>isset($offCanvasMenus)?$offCanvasMenus:[],
    'autoMenuOpeners'=>false,//open element is handled at user nav menu (header-container)
    'pageContent'=>$this->render('common.widgets.sgridlayout.views.index',[],true),
]);
