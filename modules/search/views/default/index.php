<?php
$this->registerSListViewScript();

if ($this->showSearchbar()){
    $this->widget('common.widgets.spage.SPage',array(
        'id'=>'search_bar_container',
        'layout'=>false,
        'heading'=>false,
        'linebreak'=>false,
        'body'=>$this->searchWidget($this->getSearchPlaceholder(),$this->getOnSearch(),$this->getSearchInput()),
    ));
}

$this->widget('common.widgets.spage.SPage',array(
    'id'=>'search_result_page',
    'flash'=> $this->id,
    'layout'=>false,
    'heading'=>array(
        'name'=> Sii::t('sii','Search Results'),
        'subscript'=> $query,
    ),
    'linebreak'=>false,
    'body'=>$this->getSearchResults($response),
));


if (isset($script)){
    Helper::registerJs($script,$this->id.time());
}
