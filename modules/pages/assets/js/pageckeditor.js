CKEDITOR.editorConfig = function( config ) {
    config.language = 'en';
    config.width = '100%';
    config.removePlugins = 'newpage, docprops, print, spellchecker, scayt, wsc, flash, smiley ';
    config.removeDialogTabs = 'image:advanced';
    config.extraPlugins = 'iframe';
    config.extraAllowedContent = 'div(*)';
    // Disable spellchecker
    config.disableNativeSpellChecker = true;
    config.scayt_autoStartup = false;
    // Other configurations
    config.protectedSource.push( /<\?[\s\S]*?\?>/g );   /*PHP code*/
    config.toolbar = [
        { name: 'styles',      items : [ 'Format',/*'Styles','Font',*/'FontSize' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike'/*,'Subscript','Superscript','-','RemoveFormat'*/ ] },
        { name: 'colors',      items : [ 'TextColor','BGColor' ] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',/*'CreateDiv'*/,'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
        { name: 'links',       items : [ 'Link','Unlink',/*'Anchor',*/'Image' ] },
        /*{ name: 'insert',      items : [ 'Image','HorizontalRule','SpecialChar' ] },*/
        /*'/',*/
        { name: 'document',    items : [ /*'Source','-','Preview','-',*/'Templates','Iframe' ] },
        /*{ name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },*/
        /*{ name: 'editing',     items : [ 'Find','Replace','-','SelectAll'] },*/
        /*'/',*/
        { name: 'tools',       items : [ 'Maximize', /*'ShowBlocks',*/'Preview','-','Source' ] }
    ];
    config.filebrowserImageWindowWidth = '700';
    config.filebrowserImageWindowHeight = '720';
};
