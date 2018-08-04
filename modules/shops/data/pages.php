<?php
//These are the default pages to be created when a shop is created.
return [
    'header' => [
        'title'=>'Header',
        'desc'=>'This is page header which is always placed at the top of all pages.',
        'slug'=>'__header__',
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>'header',
            'baseRoute'=>'/',//if not set default baseUrl starts with '/page'
            'locked'=>true,
            'embed'=>true,
        ],
    ],    
    'footer' => [
        'title'=>'Footer',
        'desc'=>'This is page footer which is always placed at the bottom of all pages.',
        'slug'=>'__footer__',
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>'footer',
            'baseRoute'=>'/',//if not set default baseUrl starts with '/page'
            'locked'=>true,
            'embed'=>true,
        ],
    ],    
    ShopPage::HOME => [
        'title'=>'Home',
        'desc'=>'This page is your shop\'s default landing page.',
        'slug'=>ShopPage::trimPageId(ShopPage::HOME),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::HOME,
            'baseRoute'=>'/',//if not set default baseUrl starts with '/page'
            'locked'=>true,
        ],
    ],
    ShopPage::ABOUT => [
        'title'=>'About us',
        'desc'=>'This page introduces your shop and tell what your shop is about.',
        'slug'=>ShopPage::trimPageId(ShopPage::ABOUT),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::ABOUT,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::TOS => [
        'title'=>'Terms of Service',
        'desc'=>'This page specifies the terms of service.',
        'slug'=>ShopPage::trimPageId(ShopPage::TOS),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::TOS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::PRIVACY => [
        'title'=>'Privacy Policy',
        'desc'=>'This page specifies the privacy policy.',
        'slug'=>ShopPage::trimPageId(ShopPage::PRIVACY),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::PRIVACY,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::CONTACT => [
        'title'=>'Contact us',
        'desc'=>'This page allows customers to send you any enquiry via a contact form.',
        'slug'=>ShopPage::trimPageId(ShopPage::CONTACT),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::CONTACT,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::PAYMENT => [
        'title'=>'Payment Methods',
        'desc'=>'This page automatically lists all your online payment methods with prescribed content.',
        'slug'=>ShopPage::trimPageId(ShopPage::PAYMENT),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::PAYMENT,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::SHIPPING => [
        'title'=>'Shippings',
        'desc'=>'This page automatically lists all your online shipping options with prescribed content.',
        'slug'=>ShopPage::trimPageId(ShopPage::SHIPPING),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::SHIPPING,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::PRODUCTS => [
        'title'=>'Products',
        'desc'=>'This page automatically lists all your online products.',
        'slug'=>ShopPage::trimPageId(ShopPage::PRODUCTS),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::PRODUCTS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::PROMOTIONS => [
        'title'=>'Promotions',
        'desc'=>'This page automatically lists all your online promotions.',
        'slug'=>ShopPage::trimPageId(ShopPage::PROMOTIONS),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::PROMOTIONS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::TRENDS => [
        'title'=>'Trends',
        'desc'=>'This page automatically lists various types of buying trends on your products.',
        'slug'=>ShopPage::trimPageId(ShopPage::TRENDS),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::TRENDS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::NEWS => [
        'title'=>'News',
        'desc'=>'This page automatically lists all your latest news.',
        'slug'=>ShopPage::trimPageId(ShopPage::NEWS),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::NEWS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::SITEMAP => [
        'title'=>'Sitemap',
        'desc'=>'This page automatically generates sitemap page.',
        'slug'=>ShopPage::trimPageId(ShopPage::SITEMAP),
        'status'=>Process::PAGE_ONLINE,
        'params'=>[
            'layout'=>ShopPage::SITEMAP,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],     
    ShopPage::RETURNS => [
        'title'=>'Returns Policy',
        'desc'=>'This page specifies the returns policy.',
        'slug'=>ShopPage::trimPageId(ShopPage::RETURNS),
        'status'=>Process::PAGE_OFFLINE,
        'params'=>[
            'layout'=>ShopPage::RETURNS,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
    ShopPage::REFUND => [
        'title'=>'Refund Policy',
        'desc'=>'This page specifies the refund policy.',
        'slug'=>ShopPage::trimPageId(ShopPage::REFUND),
        'status'=>Process::PAGE_OFFLINE,
        'params'=>[
            'layout'=>ShopPage::REFUND,
            'baseRoute'=>'/',
            'locked'=>true,
        ],
    ],
];