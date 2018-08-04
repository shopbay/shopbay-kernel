<?php
return [ 
    'products/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasProductLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('products'),
        'flashId'=>'Product',
        'flashMessage'=>Sii::t('sii','You have hit product limit: {limit}.'),
    ],
    'products/category/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasProductCategoryLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('product/category'),
        'flashId'=>'Category',
        'flashMessage'=>Sii::t('sii','You have hit product category limit: {limit}.'),
    ],    
    'products/category/subcategoryformget'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::patternize(Feature::$hasProductSubcategoryLimitTierN),
        'GET'=>[
            'category'=>'cid',//@see products.js for how cid is obtained
        ],
        'jsonResponseOnRejection'=>true,
        'flashId'=>'Category',
        'flashMessage'=>Sii::t('sii','You have hit product sub-category limit: {limit}.'),
    ],   
    'brands/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasProductBrandLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('brands'),
        'flashId'=>'Brand',
        'flashMessage'=>Sii::t('sii','You have hit product brand limit: {limit}.'),
    ],      
    'products/management/import'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$importProductsByFile,
        'redirectUrlOnRejection'=>url('products'),
        'flashId'=>'Product',
    ], 
];