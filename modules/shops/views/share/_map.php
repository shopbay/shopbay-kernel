<?php
Yii::import('common.extensions.egmap.EGMapBase');
Yii::import('common.extensions.egmap.EGMap');
Yii::import('common.extensions.egmap.EGMapGeocodedAddress');
Yii::import('common.extensions.egmap.EGMapMarker');
Yii::import('common.extensions.egmap.EGMapClient');
Yii::import('common.extensions.egmap.EGMapCoord');
Yii::import('common.extensions.egmap.EGMapInfoWindow');
Yii::import('common.extensions.egmap.EGMapEvent');
 
$gMap = new EGMap();
$gMap->setContainerId('shop_map');
$gMap->setContainerOptions(['class'=>'shop-map']);
$gMap->setContainerStyle('width','400');//default width; but overridden by javascript calibratemap() below
$gMap->setContainerStyle('height','400');//default height; but overridden by javascript calibratemap() below
$gMap->zoom = 15;
if (isset($options))
    $gMap->setOptions($options);

// Create geocoded address
$geocoded_address = new EGMapGeocodedAddress($longAddress);
$geocoded_address->geocode($gMap->getGMapClient());
 
// Center the map on geocoded address
 $gMap->setCenter($geocoded_address->getLat(), $geocoded_address->getLng());
 
 // Create GMapInfoWindows
$info_window = new EGMapInfoWindow('<div><b>'.$shopName.'</b><p style="font-size:0.8em">'.$longAddress.'<?p></div>');
 
$marker = new EGMapMarker($geocoded_address->getLat(), $geocoded_address->getLng());
$marker->addHtmlInfoWindow($info_window);

// Add marker on geocoded address
$gMap->addMarker($marker);
 
$gMap->renderMap();

$defaultCss = isset($defaultCss)?$defaultCss:'{width:450,height:300}';
$mobileCss = isset($mobileCss)?$mobileCss:'{width:300,height:300}';
cs()->registerScript('calibratemap_'.time(),'calibratemap('.$defaultCss.','.$mobileCss.');',CClientScript::POS_END);
