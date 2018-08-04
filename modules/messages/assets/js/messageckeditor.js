CKEDITOR.editorConfig = function( config ) {
    config.language = 'en';
    config.width = '100%';
    config.removePlugins = 'newpage, docprops, print, spellchecker, scayt, wsc, flash, smiley ';
    config.removeDialogTabs = 'image:advanced';
    // Disable spellchecker
    config.disableNativeSpellChecker = true;
    config.scayt_autoStartup = false;
    // Other configurations
    config.protectedSource.push( /<\?[\s\S]*?\?>/g );/*PHP code*/
    config.toolbar = [
        { name: 'styles',      items : [ 'Format',/*'Styles','Font',*/'FontSize' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike'/*,'Subscript','Superscript','-','RemoveFormat'*/ ] },
        { name: 'colors',      items : [ 'TextColor','BGColor' ] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',/*'CreateDiv'*/,'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
        { name: 'links',       items : [ 'Link','Unlink',/*'Anchor',*/ ] },
        /*{ name: 'tools',       items : [ 'Source' ] }*/
    ];
    config.filebrowserImageWindowWidth = '700';
    config.filebrowserImageWindowHeight = '720';
};