<?php
$this->widget('SPage',array(
    'id'=>$this->id,
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'layout'=>$this->layout,
    'sidebars'=>$this->sidebars,
    'flash'=>$this->flash,
    'loader' => array(
        'id'=>'pageindex_loader',
        'type'=>SLoader::ABSOLUTE,
    ),
    'linebreak' => false,
    'heading'=> $this->getHeading(),
    'description'=> $this->getDescription(),
    'body'=>$this->render('_body',array(),true),
    'csrfToken' => true,
));
