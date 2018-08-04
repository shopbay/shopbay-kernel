<?php
$this->widget('common.widgets.SGridView', array(
    'id'=>'oauth_networks',
    'htmlOptions'=>array('data-csrf'=>$this->csrfToken,'data-unlink-route'=>OAuthNetworks::$unlinkRoute),
    'template'=>'{items}',
    'dataProvider'=>$this->arrayDataProvider,
    'columns'=>array(
        array(
            'name' =>'connected',
            'htmlOptions'=>array('style'=>'text-align:center;width:4%'),
            'header'=>'',
            'type'=>'html',
        ),
        array(
            'name' =>'providerLink',
            'htmlOptions'=>array('style'=>'text-align:left;','class'=>'provider-link'),
            'header'=>Sii::t('sii','Network'),
            'type'=>'raw',
        ),
        array(
            'name' =>'userInfo',
            'htmlOptions'=>array('style'=>'text-align:left;','class'=>'user-info'),
            'header'=>Sii::t('sii','Account Information'),
            'type'=>'raw',
        ),
        array(
            'class'=>'CButtonColumn',
            'buttons'=> array (
                'link' => array(
                    'label'=> OAuthNetworks::linkIcon(), 
                    'imageUrl'=>false,  
                    'visible'=>'$data[\'linkable\']', 
                    'url'=>'$data[\'oauthUrl\']', 
                    'click'=>'js:'.OAuthNetworks::linkScript(),  
                ),                                    
                'unlink' => array(
                    'label'=>OAuthNetworks::unlinkIcon(), 
                    'imageUrl'=>false,  
                    'visible'=>'$data[\'unlinkable\']', 
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'js:'.OAuthNetworks::unlinkScript(),  
                ),                                    
                'signout' => array(
                    'label'=>OAuthNetworks::signoutIcon(), 
                    'imageUrl'=>false,  
                    'visible'=>'$data[\'logoutable\']', 
                    'url'=>'$data[\'logoutUrl\']', 
                    'click'=>'js:'.OAuthNetworks::signoutScript(),  
                ),                                    
            ),
            'template'=>'{link} {unlink} {signout}',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%','class'=>'oauth-network-actions'),
        ),
    ),
));
