/*!
 * Shopbay page layout editor library
 *
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */    
var CONTAINER = 'container', ROW = 'row', COLUMN = 'column', MAX_COLUMN = 12;//12 column grid layout
function Element(type, parent, content, config) {
    this.type = type;
    this.config  = config;
    this.dom;/* the dom obj */    
    this.init = function(){
        //console.log('Element '+type+' content insert = '+content.insert,content.html);
        if (content.insert=='append')
            parent.append(content.html);//fill initial draggable content into
        if (content.insert=='beforeLastRow'){
            parent.find('.row:last-child').before(content.html);//fill initial draggable content into
        }
        parent.find('.'+this.config.widgetCss).addClass(this.type+' new');/* add new widget type as css */
        this.dom = parent.find('.'+this.type+'.new');/* get new widget object */
        for(i=0;i<this.config.removeClasses.length;i++){
            this.dom.removeClass(this.config.removeClasses[i]);/*remove unwanted css class */
        }
        for(i=0;i<this.config.removeAttrs.length;i++){
            this.dom.removeAttr(this.config.removeAttrs[i]);/*remove unwanted attr */
        }
        parent.find('.widget-label').remove();/*remove unwanted widget label */
    }; 
    /* Init dom object */
    this.init();
    return this.dom;
};
function Container(selector,width) {
    this.selector = selector;
    this.width = width;
    this.dom = $(selector);
    if (this.dom.attr('id')==undefined){//assign one id if not found
        this.dom.attr('id',guid(CONTAINER));    
    }
    this.loadLayout = function(container) {
        container.find('> .sgridrow').each(function(){
            var width = $(this).parent().width();
            /* setup rows */
            $(this).addClass('widget-sgridrow');
            var rowContent = {
                html:false,/* set content to false to use back existing content*/
                insert:'beforeLastRow'
            };
            var row = new Row(container, rowContent, width);
            /* setup columns */
            row.dom.find('> .sgridcolumn').each(function(){
                $(this).addClass('widget-sgridcolumn');
                var columnContent = {
                    html:false,/* set content to false to use back existing content*/
                    insert:'append'
                };
                new Column(row.dom, columnContent);
            });    	
        });            
    };
    this.saveLayout = function(){
        var layout = [];
        this.dom.find('> .row').each(function(){
            var row = {
                name:$(this).data('name'),
                columns:[],
            };
            $(this).find('.'+$(this).data('type')+'-modal form .form-field').each(function(){
                row[$(this).attr('id')] = $(this).val();
            });

            $(this).find('> .column').each(function(){
                var column = {
                    name:$(this).data('name'),
                    type:$(this).data('type'),
                };
                if (column.type=='sgridcontainerblock'){
                    var container = new Container('#'+$(this).attr('id'),false);
                    column['rows'] = container.saveLayout();
                    //containerblock form fields 
                    $(this).find('.'+$(this).data('type')+'-modal form .form-field').each(function(){
                        column[$(this).attr('id')] = $(this).val();
                    });
                }
                else {
                    //non-containerblock form fields 
                    $(this).find('form .form-field').each(function(){
                        column[$(this).attr('id')] = $(this).val();
                        if ($(this).data('fieldType')=='html'){
                            var htmltext = $(this).val().replace(/\\n/g, '').replace(/&/g,'%26');//escape special chars
                            //console.log('Save layout field type html',htmltext);
                            column[$(this).attr('id')] = htmltext;
                        }
                        else {
                            column[$(this).attr('id')] = $(this).val();
                            
                        }
                    });
                    
                }
                row.columns.push(column);
            });
            layout.push(row);
        });        
        console.log('layout saved for '+this.dom.attr('id'),layout);
        return layout;        
    };
    this.enableDroppable = function(){
        var rowCount = this.dom.find('> .row').size();
        if (rowCount==1){
            var row = this.dom.find('> .row');
            if (row.hasClass('header') || row.hasClass('footer')){
                //console.log('container '+this.dom.attr('id')+' has header/footer row only: disable droppable');
                return false;
            }
        }
        return true;
    };
    //Auto loaded with layout when width is not false (got value)
    if (width!=false){
        this.dom.width(width);   
        /* Load existing page layout */
        this.loadLayout(this.dom);
        /* Load nested rows */
        this.loadLayout(this.dom.find('.sgridcolumn .sgridcontainerblock'));
        //Make container droppable when condition met
        if (this.enableDroppable()){
            
            this.dom.droppable({
                accept: '.row-draggable',
                greedy: true,
                drop: function( event, ui ) {
                    var content = {
                        html:$(ui.draggable).html(),
                        insert: $(this).data('type')=='sgridcontainerblock' ? 'append': 'beforeLastRow',
                    };
                    new Row( $(this), content, this.width );
                    //console.log('row dropped into container '+$(this).attr('id')+' '+$(this).data('type'));
                }
            });
        }
    }
    return this;
}
function Row(container, content, width) {
    this.parent = container;//container dom
    this.width = width;
    this.type;
    this.locked;
    this.dropdown;
    this.__config  = {'widgetCss':'widget-sgridrow', 'removeClasses':['widget-sgridrow','new','widget-label'],'removeAttrs':['title']};
    this.dom;/* the row dom is to be assigned during init */
    this.init = function(){
        this.dom = new Element(ROW, container, content , this.__config );
        if (content.html.length>0)
            this.dom.text('');/* set row to empty content - content already loaded inside Element */
        var id = guid(ROW);
        this.type = this.dom.data('type');
        this.dom.attr('id',id);
        this.locked = this.dom.data('locked');
        this.dom.prepend('<div class="widget-header"><span class="guid">'+id+'</span></div>');
        //add dropdown data when not locked
        if (this.locked){
            this.dom.find('.widget-header').prepend('<i class="fa fa-lock"></i>');
        }   
        else {
            //init modal form
            this.initModalForm();
            var dropdownData = [
                {'button':'edit','enabled':this.dom.data('edit')},
                {'button':'delete','enabled':this.dom.data('delete')}
            ];
            this.dropdown = new DropdownMenu(this, dropdownData);
        }
        //remove control data attributes
        this.dom.removeAttr('data-locked');
        this.dom.removeAttr('data-edit');
        this.dom.removeAttr('data-delete');
    }; 
    this.droppable = function(){
        this.dom.droppable({
            //accept: function(draggable){return $(draggable).hasClass("col-draggable");},
            accept: '.col-draggable',
            greedy: true,
            drop: function( event, ui ) {
                var content = {
                    html:$(ui.draggable).html(),
                    insert:'append',
                };
                new Column( $(this), content );
                //console.log('column dropped into row '+$(this).attr('id'));
            }
        });    
    };
    this.modalForm = function(){
        var form = this.dom.find('#'+this.modalFormId());
    	if (form.length==0){//widget modal form exists, based on the logic that widget form id is not yet assigned
            this.initModalForm();
        }
        return this.dom.find('#'+this.modalFormId());//find again since now widget form id is assigned
    };
    this.modalFormId = function(){
        return this.dom.attr('id')+'_modal';
    };
    this.initModalForm = function(){
        //create one from default Note: there is another template from located at sidebar which we dont want!
        var form = this.dom.find('.'+this.type+'-modal');
        form.attr('id',this.modalFormId());//assign widget form id
        form.find('form').attr('data-id',this.modalFormId());//assign modal form id
        form.find('form').attr('data-parent-id',this.dom.attr('id'));//assign widget type
        form.find('form').attr('data-type',this.type);//assign widget type
        form.find('#widget_id').text(this.dom.attr('id'));//assign widget form id follow column widget id
        //console.log('Init widget modal from; assign id '+this.modalFormId()+' and set size to '+this.size);
    };    
    /* Init dom object */
    this.init();

    /* Make row droppable */
    if (!this.locked)
        this.droppable();
    
    //console.log('Row created '+this.dom.attr('id'));
    return this;
}
function Column(row, content ) {
    this.parent = row;//row dom
    this.type;
    this.dropdown;
    this.size;
    this.locked;
    this.__config  = {'widgetCss':'widget-sgridcolumn', 'removeClasses':['widget-sgridcolumn','new','widget-label'],'removeAttrs':['title']};
    this.dom;/* the column dom is to be assigned during init */
    this.init = function(){
        this.dom = new Element(COLUMN, row, content , this.__config );
        this.type = this.dom.data('type');
        this.locked = this.dom.data('locked');
        var id = guid(this.type.substring(5));
        this.dom.attr('id',id);
        this.size = this.dom.data('size');
        this.dom.prepend('<div class="widget-header"><span class="guid">'+id+'</span></div>');
        this.dom.width( this.adaptiveWidth(this.size) );/* adjust width according to size*/
        
        if (this.locked){
            this.dom.find('.widget-header').prepend('<i class="fa fa-lock"></i>');
        }   
        else {
            //init modal form
            this.initModalForm();
            //add dropdown data when not locked
            var dropdownData = [
                {'button':'edit','enabled':this.dom.data('edit'),'key':'type','value':this.dom.data('type')},
                {'button':'delete','enabled':this.dom.data('delete')}
            ];
            this.dropdown = new DropdownMenu(this, dropdownData);
        }        
        //remove control data attributes
        this.dom.removeAttr('data-locked');
        this.dom.removeAttr('data-edit');
        this.dom.removeAttr('data-delete');
    };
    this.rowWidth = function(){
        return this.parent.width();
    };
    this.unitWidth = function(){
        return (this.rowWidth() / MAX_COLUMN) - 0;//0 is the offset
    };
    this.unitHeight = function(){
        return 350;//fixed for now
    };
    this.adaptiveWidth = function(size){
        this.size = size;
        if (this.type=='sgridfixtureblock')
            return this.unitWidth() * size - 0.5;//add offset to make it fit into row
        else if (this.type=='sgridmenublock')
            return this.unitWidth() * size - 0.3;//add offset to make it fit into row
        else
            return this.unitWidth() * size;
    };    
    this.hitLimit = function(){//this is refer to row dom
        var totalWidth = 0;
        //find the nearest column only, to prevent nested row columns
        this.parent.find('> .column').each(function(e){
            //console.log('column width = '+$(this).width());
            totalWidth += $(this).width() ; 
        });
        var totalSize = Math.ceil(totalWidth / this.unitWidth() );
        //console.log('Total column size = '+totalSize+ ' , unit width = '+ this.unitWidth());
        if (totalSize > MAX_COLUMN ){
            console.log('Total column size '+totalSize+' exceeds max '+MAX_COLUMN+'.');
            return true;
        }
        else {
            return false;
        }
    };
    this.hasHitSizeLimit = function(newSize){//this is to check if adding new column size still allowed
        var totalSizeBefore = this.totalColumnSize();
        var newTotalSize = totalSizeBefore + parseInt(newSize) - this.size;//minus out current size
        if ( newTotalSize > MAX_COLUMN ){
            console.log('Total column size '+newTotalSize+' exceeds max '+MAX_COLUMN+'.  new size = '+newSize+', current size = '+this.size+' , column total size before = '+totalSizeBefore);
            return true;
        }
        else {
            return false;
        }
    };   
    this.totalColumnSize = function(){//this solely return the sum of column size of parent row
        var total = 0;
        this.parent.find('> .column').each(function(e){
            total += parseInt($(this).attr('data-size')); /* note: it seems using data(field) got memory and not returning latest value*/
        });
        return total;
    };       
    this.adjustSize = function(size) {
        this.dom.removeClass(function(index, css){
            return (css.match(/(^|\s)col-md-\S+/g) || []).join(' ');//use regex to remove class starting with "col-md-"
        });
        this.dom.addClass('col-md-'+size);/*auto adjust size*/
        this.dom.attr('data-size',size);
        /* update widget form value , field value is hardcoded to 'size' for now*/
        this.modalForm().find('#size').val(size);
        this.dom.width(this.adaptiveWidth(size));/* set adaptive width  */
    };
    this.modalForm = function(){
        var form = this.dom.find('#'+this.modalFormId());
    	if (form.length==0){//widget modal form exists, based on the logic that widget form id is not yet assigned
            this.initModalForm();
        }
        return this.dom.find('#'+this.modalFormId());//find again since now widget form id is assigned
    };
    this.modalFormId = function(){
        return this.dom.attr('id')+'_modal';
    };
    this.initModalForm = function(){
        //create one from default Note: there is another template from located at sidebar which we dont want!
        var form = this.dom.find('.'+this.type+'-modal');
        form.attr('id',this.modalFormId());//assign widget form id
        form.find('form').attr('data-id',this.modalFormId());//assign modal form id
        form.find('form').attr('data-parent-id',this.dom.attr('id'));//assign widget type
        form.find('form').attr('data-type',this.type);//assign widget type
        form.find('#widget_id').text(this.dom.attr('id'));//assign widget form id follow column widget id
        form.find('#size').val(this.size);//sync up column size
        //console.log('Init widget modal from; assign id '+this.modalFormId()+' and set size to '+this.size);
    };
    this.resizable = function(){
        var column = this;
        this.dom.resizable({
            autoHide: true,
            containment: "parent",
            grid: [ column.unitWidth(), column.unitHeight()],
            maxWidth: column.rowWidth(),
            minWidth: column.unitWidth() ,
            start: function( event, ui ) {},
            stop: function(event, ui) {                            
                /* count if total column width hits max */    
                if ( column.hitLimit() ){
                    console.log('Resize not allowed, rollback to original width.');
                    ui.element.width( ui.originalSize.width);
                }
                else {
                   /* adding some offset to allow one column size possible */
                    var offset =  10;
                    var actualWidth = ui.size.width - offset;
                    var size = Math.ceil( actualWidth / column.unitWidth() );
                    column.adjustSize(size);
                    ui.element.width( column.adaptiveWidth(size) );/*auto adjust width according to size*/
                    console.log('width = '+actualWidth+', one column width = '+column.unitWidth()+', Resize to '+size);
                }
            },
            resize: function(event, ui) {},
        });
    };
    this.droppable = function(){        
	var columnId = this.dom.attr('id');
	//console.log('Make column a container droppable: '+columnId);
        new Container('#'+columnId,this.dom.width()); /* make column itself a container to accept row */
    };
    /* Init dom object */
    this.init();

    /* Check column limit; If hit, reject */
    if (this.totalColumnSize() > MAX_COLUMN ){
    //if (this.hitLimit() ){//hitLimit check by width; Sometime width computation might not be accurate?
        this.dom.remove();
        alert('Row '+this.parent.attr('id')+' is full. Adding new column not allowed.');
        console.log('Reject adding new column into row '+this.parent.attr('id'));
        return;
    }

    if (!this.locked){
        /* Make column resizable */
        this.resizable();
        /* Make column droppable - to accept nested block */
        this.droppable();
    }    
    //console.log('column created '+this.dom.attr('id')+' with size '+this.size);
    return this;
}
function DropdownMenu(block,data) {
    this.data = data;
    this.block = block;
    this.dom;/* to be assigned after init */
    this.init = function () {
        var block = this.block;
        var parent = this.block.dom;
        var html = $('#widget_dropdown_template').html();//select from template
        parent.find('.widget-header').prepend(html);
        var dom = parent.find('.config-menu');
        dom.attr('id',parent.attr('id')+'_dropdown');//assign dropdown id
        this.data.forEach(function(d, i) {
            if (d.enabled)
                dom.find('li a.'+d.button).attr('data-'+d.key,d.value);//populate data attributes
            else
                dom.find('li a.'+d.button).remove();//remove when button is disabled.
        });        

        if (dom.find('li a.delete').length){
            dom.find('li a.delete').click(function() { //enable trash button
                parent.remove();
            });
        }
        if (dom.find('li a.edit').length){
            dom.find('li a.edit').click(function() { //enable edit button

                var widgetForm = block.modalForm();
                var widgetFormId = block.modalFormId();
                var modal = new Modal('#widget_modal_template');

                modal.copyFrom(widgetForm);
                //console.log('['+widgetFormId+'] Edit modal: Copy widget form content to modal template');

                modal.show();

                /* enable modal save button, off and on to ensure only one save click */
                modal.save(function(){

                    modal.removeFlash();

                    //todo: Might need to do validation at the backend
                    var size = modal.size();
                    //console.log('['+widgetFormId+'] Save modal: block type '+block.type);
                    if (block.type!='sgrid'+ROW){
                        //console.log('['+widgetFormId+'] Save modal: hasHitSizeLimit check '+size);
                        if (block.hasHitSizeLimit(size) ){/* size has changed but hit limit; Reject */
                            modal.flashError('Max columns hit. Resize not allowed.');
                            return;
                        }
                    }

                    modal.copyTo(widgetForm.find('form'));
                    console.log('['+widgetFormId+'] Save modal: save widget form '+widgetForm.attr('id'));

                    modal.saveBlock(parent);
                    console.log('['+widgetFormId+'] Save modal: save block '+parent.data('type')+' '+parent.attr('id'));

                    if (size!=block.size){/* size has changed */
                        console.log('['+widgetFormId+'] Save modal: adjust block size to '+size);
                        block.adjustSize(size);
                    }

                    modal.hide();

                });  
            });
        }        
        this.dom = dom;
    };
    this.init();
    return this.dom;
};
function Modal(id) {
    this.id = id;
    this.dom = $(id);
    this.widget = $('#'+this.dom.find('form').data('parentId'));/* only valid after loadContent is called */
    this.show = function(){
        this.removeFlash();/* remove all flash first*/
        this.dom.show();
    };
    this.hide = function(){
        this.dom.modal('hide');
    };
    this.softHide = function(){
        /* it seems use this.hide() has issue to open modal again, so need to use method below */
        this.dom.hide();
    };
    this.removeFlash = function(){
        this.dom.find('.modal-dialog .alert').remove();
    };
    this.flashError = function(message){
        var template = $('#widget_modal_flash_template').html();
        var title = '<strong>Oops!</strong>';
        this.dom.find('.modal-dialog').prepend(template);
        this.dom.find('.modal-dialog .alert').addClass('alert-danger');
        this.dom.find('.modal-dialog .alert').append(title+' '+message);
    };
    this.size = function(){
        return this.dom.find('#size').val();
    }; 
    this.save = function(event){
        this.dom.find('.btn-primary').off().click(event);
    };
    this.saveBlock = function (block){
        this.dom.find('form .form-field').each(function(){
            var field = $(this).data('field');
            //console.log('Block: copy field "'+field+'" from "'+source.attr('id')+'" to block '+block.data('type')+' '+block.attr('id'));
            if ($(this).data('fieldType')=='text'){
                block.find('.'+block.data('type')+' #'+field).text($(this).val());
            }
            else if ($(this).data('fieldType')=='language'){
                var value = JSON.parse($(this).val());
                var locale = $('.layout-editor').data('locale');//current page locale
                block.find('.'+block.data('type')+' #'+field).text(value[locale]);
                //console.log('Save block language field '+field+': ', value);
            }
            else if ($(this).data('fieldType')=='menu'){
                var menu = JSON.parse($(this).val());
                var locale = $('.layout-editor').data('locale');//current page locale
                block.find('.'+block.data('type')+' ul li').remove();//remove, reload again
                $.each(menu,function(index,item){
                    var li  = '<li>';
                        li += '<a href="'+item.url+'">';
                        li += '<span>'+item.label[locale]+'</span>';
                        li += '</a>';
                        li += '</li>';
                    block.find('.'+block.data('type')+' ul').append(li);
                    //console.log('Save block menu field '+item.id+': ', item.url);
                });
            }           
            else if ($(this).data('fieldType')=='paragraph'){
                var text = JSON.parse($(this).val());
                var locale = $('.layout-editor').data('locale');//current page locale
                block.find('.'+block.data('type')+' p').remove();//remove, reload again
                $.each(text,function(index,item){
                    var p  = '<p class="text-wrapper">';
                        p += item[locale];
                        p += '</p>';
                    block.find('.'+block.data('type')).append(p);
                    //console.log('Save block text paragraph field '+item.locale, item);
                });
            }           
            else if ($(this).data('fieldType')=='link'){
                block.find('.'+block.data('type')+' #'+field).attr('href',$(this).val());
            }
            else if ($(this).data('fieldType')=='html'){
                var value = JSON.parse($(this).val());
                var locale = $('.layout-editor').data('locale');//current page locale
                //console.log('Save block html field '+field+': ', value[locale]);
                block.find('.'+block.data('type')+' #'+field).html(value[locale].replace(/\n/g, "<br />"));
            }
            else if ($(this).data('fieldType')=='category'){
                console.log('Category selected: '+$(this).val());
		block.find('.catalog-container').html(getspinner());/*catalog-container is a system assigned value*/
                $.ajax({
                    url: $('.layouteditor-category').data('url')+'/id/'+$(this).val()+'/itemsperrow/'+block.find('form #itemsPerRow').val()+'/limit/'+block.find('form #itemsLimit').val(),
                    data: $('#layout_editor_form').serialize(),//get CSRF_TOKEN
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        console.log('Category postback: '+data.id+', '+data.items_per_row+', '+data.items_limit);
                        block.find('.catalog-container').replaceWith(data.html);
                    }
                });
            }
            else if ($(this).data('fieldType')=='listItem'){
                console.log('List item selected: '+$(this).val());
		block.find('.catalog-container').html(getspinner());/*catalog-container is a system assigned value*/
                $.ajax({
                    url: $('.layouteditor-listitem').data('url')+'/item/'+$(this).val()+'/itemsperrow/'+block.find('form #itemsPerRow').val()+'/limit/'+block.find('form #itemsLimit').val(),
                    data: $('#layout_editor_form').serialize(),//get CSRF_TOKEN
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        console.log('List item postback: '+data.id+', '+data.items_per_row+', '+data.items_limit);
                        block.find('.catalog-container').replaceWith(data.html);
                    }
                });
            }            
            else if ($(this).data('fieldType')=='boxImage'){
                block.find('.'+block.data('type')+' #'+field).attr('src',$(this).val());
                console.log('Set box image '+field+', value = '+  $(this).val());
            }
            else if ($(this).data('fieldType')=='bgImage'){
                var bgImageUrl = 'url('+$(this).val()+')';
                if (bgImageUrl=='url()'){/* empty bgImage has value 'url()' */
                    /* no bgImage set; reset to default */
	            bgImageUrl = $('.widget-sgridcolumn.imageblock').css('background-image');
                    block.find('.single-image-container .template-download.empty').show();
                    block.find('.single-image-container .template-download.image').remove();
                    //console.log('Reset to default bgImage '+  bgImageUrl);
                }
                var css = {'background-repeat':'no-repeat','background-image':bgImageUrl};
                block.find('.'+block.data('type')).css(css);/* update widget block css*/
                console.log('Set block style by bgImage: '+  block.attr('style'));
            }
            else {
                block.find('.'+block.data('type')+' #'+field).text($(this).val());
            }
            
            /* apply css style*/
            if (field=='style'){
                var previousStyle = block.find('.'+block.data('type')).attr('style');
                block.find('.'+block.data('type')).attr('style', previousStyle+';'+$(this).val());/* prepend previous styles; new style will override previous style */
                console.log('Direct block style override: '+  $(this).val());
            }
        });
        /* sync multi image container if any */
    	if (this.onMultiImage()){
            var carouselIndicators = block.find('.'+block.data('type')+' .carousel-indicators');
            var carouselInner = block.find('.'+block.data('type')+' .carousel-inner');
            carouselIndicators.html('');/* remove all items first and reload */
            carouselInner.html('');/* remove all items first and reload */
            var locale = $('.layout-editor').data('locale');//current page locale
            block.find('form .slide-field-group').not('.template').each(function(index){
                var indicator = '<li data-slide-to="'+index+'" data-target="'+block.find('.'+block.data('type')+' .carousel').attr('id')+'">';
                var item = '<div class="item">';
                var caption='',text = '', url='#', label='';
                $(this).find('.slide-field').each(function(){
                    if ($(this).data('field')=='slideImage'){
                        item += '<img class="slide-image" src="'+$(this).val()+'">';
                    }  
                    if ($(this).data('field')=='slideText'){
                        var value = JSON.parse($(this).val());
                        text = value[locale];
                    }  
                    if ($(this).data('field')=='slideCtaLabel'){
                        var value = JSON.parse($(this).val());
                        label = value[locale];
                    }  
                    if ($(this).data('fieldName')=='slideCtaUrl'){//here use "fieldName" as data('fiel') value is changed during moving content
                        url = $(this).val();
                    }  
                });
                if (text.length>0){
                    caption += '<p>'+text+'</p>';
                }
                if (label.length>0){
                    caption += '<a href="'+url+'">'+label+'</a>';
                }
                if (caption.length>0){
                    item += '<div class="carousel-caption"><div class="caption-wrapper">'+caption+'</div></div>';
                }
                item += '</div>';
                indicator += '</li>';
                carouselIndicators.append(indicator);
                carouselInner.append(item);
                //console.log('carousel add item: '+ item +' , indicator: '+indicator);
            });
            block.find(".carousel-indicators li:first").addClass("active");
            block.find(".carousel-inner .item:first").addClass("active");
            block.find(".carousel").carousel();
    	}
    };
    this.copyTo = function(form){
    
        /* detect ckeditor for html block */
        var modal = this;
        this.dom.find('form textarea.language-field-html').each(function(){
            modal.getCkeditorData($(this));
        });
        
        /* formulate language fields before copy to widget form */
        this.dom.find('.language-field').each(function(){
            var field = $(this).data('field');
            var value = {};
            $(this).parent().find('.language-field-'+field).each(function(){
                value[$(this).data('locale')] = $(this).val();
            });
            $(this).val(JSON.stringify(value));
            //console.log('copyTo: language field '+field,$(this).val());
        });    
        
        /* formulate menu fields before copy to widget form */
        this.dom.find('.menu-field').each(function(){
            var menu = [];
            $(this).parent().find('.menu-item-field option:selected').each(function(){
                if ($(this).val()!='0'){
                    menu.push({
                        'id' : $(this).val(),
                        'type' : $(this).data('type'),
                        'url' : $(this).data('url'),
                        'label' :$(this).data('label'),
                    });
                }
            });
            //console.log('Form: menu field',menu);
            $(this).val(JSON.stringify(menu));
            /* sync the target form modal menu select items */
            var targetMenuCount = form.find('.menu-selections li').size();
            var diff = menu.length - targetMenuCount;
            //console.log('Menu item count = '+menu.length+',  target form menu item count '+targetMenuCount);
            if (diff < 0){//need to remove extra
                form.find('.menu-selections li').each(function(index){
                    if (index >= -diff){//put negative back to +ve
                        $(this).remove();
                        //console.log('Remove target menu item '+index);
                    }
                });
            }
            else if (diff > 0) {//need to add more, the value will be done via moveContent()
                var ol = form.find('.menu-selections ol');
                for (i = 0; i < diff; i++) { 
                    modal.addMenuSelect(ol,0);
                    //console.log('Add target menu item '+i);
                }
            }
        });    

        /* formulate text paragraph fields before copy to widget form */
        this.dom.find('.paragraph-field').each(function(){
            var text = [];
            $(this).parent().find('.textarea-wrapper').each(function(){
                var paragraph = {};
                $(this).find('textarea').each(function(){
                    var locale = $(this).data('locale');
                    paragraph[locale] = $(this).val();
                });
                //console.log('paragraph found ', paragraph);
                text.push(paragraph);
            });
            //console.log('Form: text field',text);
            $(this).val(JSON.stringify(text));
            /* sync the target form modal text paragraph items */
            var targetParagraphCount = form.find('.paragraph-wrapper').size();
            var diff = text.length - targetParagraphCount;
            //console.log('text count = '+text.length+',  target form paragraph count '+targetParagraphCount);
            if (diff < 0){//need to remove extra
                form.find('.paragraph-wrapper').each(function(index){
                    if (index > -diff){//put negative back to +ve
                        $(this).remove();
                        //console.log('Remove target paragraph '+index);
                    }
                });
            }
            else if (diff > 0) {//need to add more, the value will be done via moveContent()
                var p = form.find('.paragraph-wrapper:nth-of-type(1)');
                for (i = 0; i < diff; i++) { 
                    modal.addTextParagraph(p);
                    //console.log('Add target paragraph '+i);
                }
            }
        });    

        /* change slide cta url field id  before copy to widget form */
        this.dom.find('.slide-cta-url').each(function(){
            $(this).removeData('field');//remove field and add back - avoid caching issue?
            $(this).attr('data-field',$(this).attr('id'));
            //console.log('copyTo: Change slide cta url field to '+$(this).attr('id'));
        });    

        /* replace the whole image gallery*/
    	if (this.onMultiImage()){
            /* copy the next image num */
    	    form.find('.image-form-wrapper.multiple').attr('data-next-num', this.dom.find('form .image-form-wrapper.multiple').attr('data-next-num'));
            /* this already include newly added or deleted images
             * let the moveContent handles the fields values */
    	    form.find('.image-form-wrapper.multiple').html(this.dom.find('form .image-form-wrapper.multiple').html());
            //console.log('copyTo: After copy image gallery content',form.find('.image-form-wrapper.multiple').html());
        }
               
        /* copy data fields to widget form */
        this.moveContent(this.dom.find('form'),form);
        
     	/* update single image container */
    	if (this.onSingleImage()){
	    form.find('form .single-image-container').html(this.dom.find('form .single-image-container').html());
	}
    	
    };
    this.copyFrom = function(form){    
        /* load form html first */
        this.loadContent(form.html());
        /* copy data fields from widget form */
        this.moveContent(form.find('form'),this.dom.find('form'));
        /* load ckeditor for html block */
        var modal = this;
    	this.dom.find('form textarea.language-field-html').each(function(){
    	    modal.enableCkeditor($(this));
        });
        /* restore language fields */
        this.dom.find('form .language-field').each(function(){
            var field = $(this).data('field');
            //console.log('language-field found '+field,$(this).val());
            if ($(this).val().length>0){
                var value = JSON.parse($(this).val());
                $(this).parent().find('.language-field-'+field).each(function(){
                    $(this).val(value[$(this).data('locale')]);
                    //console.log('Restore language field '+field+ ' with locale '+$(this).data('locale'),$(this).val());
                });
            }
        });    
        /* restore menu fields */
        this.dom.find('form .menu-field').each(function(){
            var menu = JSON.parse($(this).val());
            $.each(menu,function(index, item){
                modal.dom.find('form .menu-selections li:nth-of-type('+(parseInt(index)+1)+') select').val(item.id);
                //console.log('Restore menu item '+index,item);
            });
        });    
        /* restore text fields */
        this.dom.find('form .paragraph-field').each(function(){
            var text = JSON.parse($(this).val());
            $.each(text,function(index, item){
                modal.dom.find('form .textarea-wrapper').each(function(key,element){
                    if (key==index){//match same index 
                        $(this).find('textarea').each(function(){
                            $(this).val(item[$(this).data('locale')]);
                            //console.log('Restore paragraph field '+$(this).attr('id')+ ' with locale '+$(this).data('locale'),$(this).val());
                        });
                    }
                });
                //console.log('Restore text paragraph '+index,item);
            });
        });    
        
        /* load single image container */
    	if (this.onSingleImage()){
            var imageField = form.find('form #'+this.singleImageField());
            if (imageField.length>0){/* got image found ! */
                var url = imageField.val();
                if (url.length>0){
                    this.addSingleFileTemplate(this.dom.find('.single-image-container'),url);
                    console.log('Set preview image '+imageField.attr('id')+', value = '+ url);
                    this.onSingleImageDelete(imageField.attr('id'));
                }
            }
        }        
        /* load multi image container */
    	if (this.onMultiImage()){
            this.onMultiImageDelete();
        }        

    };    
    this.moveContent = function(source,destination){
        /* copy all form fields to widget form */
        source.find('.form-field').each(function(){
            var field = $(this).data('field');
            //console.log('Form: field "'+field, $(this).html());
            if ($(this).data('fieldType')=='text'){
                destination.find('#'+field).text($(this).text());
                //console.log('Move content: text field type ' + field + ', value = '+ $(this).text());
            }
            else {
                destination.find('#'+field).val($(this).val());
                //console.log('Move content: copy field "'+field+'" value "'+$(this).val()+'" from "'+source.attr('id')+'" to '+destination.data('id'));
            }
        });       
    };    
    this.loadContent = function(content){
        //make the modal dialog wider to give more space 
    	this.dom.find('.modal-dialog').css({'width':'80%'});
        this.dom.find('.modal-content').html('');/*clear previous content*/
        this.dom.find('.modal-content').html(content);
        this.enableTabs('modal');
        this.enableTabs('language');
        /* enable file upload button */
    	if (this.onSingleImage()){
            this.onSingleFileUpload(this.id);
        }
    	if (this.onMultiImage()){
            this.onMultiFileUpload(this.id);
        }
        /* enable menu add/remove button */
        this.enableMenuSelectControl();
        
        /* enable text paragraph add/remove button */
        this.enableTextParagraphControl();
    };
    /**
     * Below handles the SingleImageForm widget for uploading/deletion
     */
    this.onSingleFileUpload = function(modalId) {
        var form = this.dom.find('form');
        var field = this.singleImageField();
        form.find('input:file').fileupload({
            dataType: 'json',
            url: form.attr('action'),
            autoUpload:true,
            previewMaxWidth:30,
            previewMaxHeight:30,
            maxNumberOfFiles:1,
            disableImageResize:'/Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent)',
            maxFileSize:999000,
            done: function (e, data) {
                var filesContainer = form.find('.single-image-container.files');
                var modal = new Modal(modalId);
                filesContainer.find('.template-download.spinner').remove();
                /* Here the template-download is not auto load (got issues) - have to re-compose here */
                $.each(data.result.files, function (index, file) {
                    modal.addSingleFileTemplate(filesContainer,file.thumbnail_url);
    	            form.find('#'+field).val(file.thumbnail_url);/*set imag field url */
                });
                modal.onSingleImageDelete(field);/* standby image delete */
            },
            progress:function(e, data){
                var filesContainer = form.find('.single-image-container.files');
                showspinner_singleimage(filesContainer);
            },
            processstart: function(e){
                console.log('Processing started...');
            },
            process: function(e,data){
                console.log('Processing ' + data.files[data.index].name + '...');
            },
            processalways: function(e,data){
                console.log('Processing ' + data.files[data.index].name + ' ended.');
            },
            processfailed: function(e,data){
                console.log('Processing ' + data.files[data.index].name + ' failed.');
            },
        });
    };   
    this.onMultiFileUpload = function(modalId) {
        var form = this.dom.find('form');
        form.find('input:file').fileupload({
            dataType: 'json',
            url: form.attr('action'),
            autoUpload:true,
            previewMaxWidth:30,
            previewMaxHeight:30,
            maxNumberOfFiles:1,
            disableImageResize:'/Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent)',
            maxFileSize:999000,
            done: function (e, data) {
                var filesContainer = form.find('.images-gallery table tbody');
                var modal = new Modal(modalId);
                filesContainer.find('.spinner').remove();
                /* Here the template-download is not auto load (got issues) - have to re-compose here */
                $.each(data.result.files, function (index, file) {
                    var nextNum = modal.dom.find('.image-form-wrapper.multiple').attr('data-next-num');
                    modal.addSlideRowTemplate(nextNum,file.thumbnail_url);
                });
            },
            progress:function(e, data){
                var filesContainer = form.find('.images-gallery table tbody');
            	showspinner_multiimage(filesContainer);
            },
            processstart: function(e){
                console.log('Processing started...');
            },
            processfailed: function(e,data){
                console.log('Processing ' + data.files[data.index].name + ' failed.');
            },
        });
    };       
    this.onSingleImage = function() {
    	return this.dom.find('.single-image-container').length;
    };   
    this.onMultiImage = function() {
    	return this.dom.find('.images-gallery table').length;
    };   
    this.singleImageField = function() {
        var bgImage = this.dom.find('form #bgImage');
        var boxImage = this.dom.find('form #boxImage');
        if (bgImage.length>0){/* got bgImage ! */
            return 'bgImage';
        }
        else if (boxImage.length>0){/* got boxImage ! */
            return 'boxImage';
        }   
        else {
            return 'unknown_image';
        }
    };
    this.onSingleImageAdd = function(url) {
    	this.show();
    	showspinner_singleimage(this.dom.find('form .single-image-container.files'));
        $.ajax({
            modal : this, /* custom variable pass into ajax call */
            imageField : this.singleImageField(), /* custom variable pass into ajax call */
            url: url,
            dataType: 'json',
            cache: false,
            success: function(data) {
                if (data.status=='success'){
                    $('.page-loader').hide();
                    this.modal.dom.find('.single-image-container .template-download.spinner').remove();
                    this.modal.dom.find('.single-image-container').append(data.html);
                    var image = this.modal.dom.find('.single-image-container .template-download.image:visible img');
                    this.modal.dom.find('form #'+this.imageField).val(image.attr('src'));/*set bgImage url */
                    //console.log('Set single image add: '+this.imageField,image.attr('src'));
                    this.modal.onSingleImageDelete(this.imageField);/* standby image delete */
                }
                else {
                    alert(data.message);
                }
            }
        });    
    };
    this.onSingleImageDelete = function(field) {
        var modal = this;
        this.dom.find('#delete_button').off().click(function() {
            modal.dom.find('.single-image-container .template-download.image').remove();
            modal.dom.find('.single-image-container .template-download.empty').show();
            modal.dom.find('form #'+field).val('');/*set to null*/
        });
    };
    this.onMultiImageDelete = function() {
        var modal = this;
        modal.dom.find('form .images-gallery .template-download.image button.btn-danger').each(function(){
            $(this).off().click(function() {
                $(this).parent().parent().remove();/* remove row */
                var slideImageField = modal.dom.find('form #'+$(this).data('image'));
                console.log('image modal delete: '+$(this).data('image'));
                slideImageField.parent().remove();/* remove slide group wrapper altogether */
                if (modal.dom.find('.images-gallery .template-download.image').not('.template').length==0){
                    modal.dom.find('.images-gallery table tr.empty').show();
                }
            });
        });
    };    
    this.onMultiImageAdd = function(url) {
    	this.show();
    	var nextNum = this.dom.find('form .image-form-wrapper.multiple').attr('data-next-num');
    	console.log('Image add: sending next num '+nextNum);

    	showspinner_multiimage(this.dom.find('form .images-gallery table tbody'));
        $.ajax({
            modal : this, /* custom variable pass into ajax call */
            url: url+'&next='+nextNum,
            dataType: 'json',
            cache: false,
            success: function(data) {
                if (data.status=='success'){
                    $('.page-loader').hide();
                    /* update modal */
                    this.modal.dom.find('.files tr.empty').hide();
                    this.modal.dom.find('.images-gallery .template-download.spinner').remove();
                    this.modal.addSlideRowTemplate(data.next_num,data.image_url);
                }
                else {
                    alert(data.message);
                }
            }
        });    
    };        
    /* set new input field values from template based on next_num */
    this.setNewSlideFields = function(imageUrl,nextNum) {
        //look for tr with class "new-group"
        this.dom.find('.image-form-wrapper.multiple .slide-field-group.new-group .form-field.template').each(function(){
            var field = $(this).data('field');
            if ($(this).data('field')=='slideImage'){//set image url
                $(this).val(imageUrl);
                $(this).parent().find('img').attr('src',imageUrl);
                console.log('Set slide img src to '+imageUrl);
            }
            if ($(this).data('fieldType')=='language'){//set language field id
                $(this).parent().find('.language-field-'+field).each(function(){
                    $(this).attr('id',field+'_'+nextNum+'_'+$(this).data('locale'));
                    $(this).attr('name',field+'_'+nextNum+'['+$(this).data('locale')+']');
                });
            }
            
            $(this).attr('id',field+'_'+nextNum);
            $(this).attr('name',field+'['+nextNum+']');
            $(this).removeClass('template');
            //console.log('Set slide field '+field+'_'+nextNum);
        });
    };
    this.addSingleFileTemplate = function(filesContainer,imageurl) {
        filesContainer.find('.template-download.empty').hide();
        filesContainer.find('.template-download.image').remove();//clear first and reload
        var  html  = '<div class="template-download image">';
             html += '  <div class="delete">';
             html += '    <img src="'+imageurl+'">';
             html += '    <button type="button" id="delete_button" class="btn btn-danger">';
             html += '      <i style="cursor:pointer" class="fa fa-times"></i>';
             html += '    </button>';
             html += '  </div>';
             html += '</div>';
        filesContainer.append(html);
    };
    this.addSlideRowTemplate = function(nextNum, image) {
        var template = this.dom.find('.images-gallery .slide-field-group.template').clone();
        var newGroup = '<tr class="template-download image slide-field-group new-group">'+template.html()+'</tr>';//add class "new-group" as identifier
        this.dom.find('.images-gallery table tbody').append(newGroup);
        this.dom.find('.images-gallery .template-download .preview').show();
        this.setNewSlideFields(image,nextNum);
        //after slide fields setting, remove 'new-group' class identifier
        this.dom.find('.image-form-wrapper.multiple .slide-field-group').removeClass('new-group');
        this.dom.find('.image-form-wrapper.multiple').attr('data-next-num', parseInt(nextNum) + 1);
        this.onMultiImageDelete();/* standby image delete */
        this.enableTabs('language');
    };
    this.enableCkeditor = function(textarea){
        /* change id so that to make it unique - there are many same field 'html' */
        textarea.attr('id','html_ckeditor_'+textarea.data('locale'));
        textarea.attr('name','html_ckeditor_'+textarea.data('locale'));
        CKEDITOR.replace( textarea.attr('id'), {
            filebrowserImageUploadUrl : $('.pageckeditor-imageupload').data('url')+'?APP_CSRF_TOKEN='+$('#layout_editor_form').find('input[name="APP_CSRF_TOKEN"]').val(),
            customConfig : $('.pageckeditor-asset').data('url'),
        });
        //console.log('Ckeditor loaded: textarea '+textarea.attr('id'));
    };
    this.getCkeditorData = function(textarea) {
        var locale = textarea.data('locale');
        var data = CKEDITOR.instances[textarea.attr('id')].getData();
        //console.log('Ckeditor data capture: '+data);
        /* change back id and assign data so that follow default move content behavior */
        textarea.attr('id','html_'+locale);//any id (not 'html' will do)
        textarea.attr('name','html'+locale);
        textarea.val(data);
    };
    this.enableTabs = function(tabgroup) {
        this.dom.find('.'+tabgroup+'-tabs a').off().click(function (e) {
            e.preventDefault();
            console.log('Start '+tabgroup+' tab click: '+$(this).attr('href'));
            /*Need to manually activate each tabs */
            $(this).parent().parent().parent().find('.'+tabgroup+'-tab-content .tab-pane').removeClass('active');
            $(this).parent().parent().parent().find('.'+tabgroup+'-tab-content .tab-pane'+$(this).attr('href')).addClass('active');
        });
        this.dom.find('.'+tabgroup+'-tabs li').removeClass('active');
        this.dom.find('.'+tabgroup+'-tabs li:first-child').addClass('active');
        this.dom.find('.'+tabgroup+'-tab-content .tab-pane').removeClass('active');
        this.dom.find('.'+tabgroup+'-tab-content .tab-pane:first-child').addClass('active');
    };
    this.enableMenuSelectControl = function() {
    	if (this.dom.find('form .menu-selections').length){
            var modal = this;
            this.dom.find('form .select-control.add-btn').off().click(function (e) {
                var ol = $(this).parent().parent();
                modal.addMenuSelect(ol,0);
            });
            this.dom.find('form .select-control.remove-btn').off().click(function (e) {
                $(this).parent().remove();
            });
        }
    };    
    this.addMenuSelect = function(ol,selected) {
        var li = ol.find('li:nth-of-type(1)').clone();
        li.addClass('new-menuitem');
        ol.append(li);
        ol.find('li.new-menuitem select').val(selected);
        ol.find('li.new-menuitem .add-btn').remove();
        ol.find('li.new-menuitem .remove-btn-template').addClass('remove-btn');
        ol.find('li.new-menuitem .remove-btn').removeClass('remove-btn-template');
        ol.find('li.new-menuitem .remove-btn').show();
        ol.find('li.new-menuitem .remove-btn').click(function (e) {
            $(this).parent().remove();
        });
        li.removeClass('new-menuitem');
    };       
    this.enableTextParagraphControl = function() {
    	if (this.dom.find('form .text-paragraphs').length){
            var modal = this;
            this.dom.find('form .select-control.add-btn').off().click(function (e) {
                var paragraph = $(this).parent();
                modal.addTextParagraph(paragraph);
            });
            this.dom.find('form .select-control.remove-btn').off().click(function (e) {
                $(this).parent().remove();
            });
        }
    };    
    this.addTextParagraph = function(paragraph) {
        var parent = paragraph.parent();
        var p = paragraph.clone();
        p.addClass('new-paragraph');
        parent.append(p);
        parent.find('.new-paragraph textarea').val('');
        parent.find('.new-paragraph .add-btn').remove();
        parent.find('.new-paragraph .remove-btn-template').addClass('remove-btn');
        parent.find('.new-paragraph .remove-btn').removeClass('remove-btn-template');
        parent.find('.new-paragraph .remove-btn').show();
        parent.find('.new-paragraph .remove-btn').click(function (e) {
            $(this).parent().remove();
        });
        p.removeClass('new-paragraph');
    };       
}
/* generate a unique id based on random number */
function guid(prefix){
    return prefix+'_'+Math.floor((1 + Math.random()) * 0x10000).toString(16);
}
/**
 * Add spinner (loading icon) for single image container
 */
function showspinner_singleimage(container){
    container.find('.template-download.empty').hide();/* hide any current preview */
    container.find('.template-download.image').hide();/* hide any current preview */
    container.append('<div class="template-download spinner">'+getspinner()+'</div>');
}
/**
 * Add spinner (loading icon) for multi image container
 */
function showspinner_multiimage(container){
    container.find('tr.empty').hide();
    container.append('<tr class="template-download spinner"><td width="100%" class="preview" colspan="2">'+getspinner()+'</td></tr>');
}
function getspinner(){
    return '<div class="spinner"><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span></div>';
}
/**
 * This method customizes getmediagalleryform() at simagegallery.js
 */
function mediagalleryform_singleimage(url) {
    mediagalleryform_open(url,false);
}
function mediagalleryform_multiimage(url) {
    mediagalleryform_open(url,true);
}
function mediagalleryform_open(url,multiple) {

    $('.page-loader').show();
    var modal = new Modal('#widget_modal_template');
    modal.softHide();

    if ($('#media_gallery_modal').length>0)
        $('#media_gallery_modal').remove();  
    
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            $('.page-loader').hide();
            $('.page-container .smodal-wrapper').append(data.modal);
            $(document).ready(function () {
                console.log(' image multiple mode = '+multiple);
                if (multiple)
                    setmediagallery_multiimage();
		else 
		    setmediagallery_singleimage();
            });            
        }
    });
}
/**
 * This method customizes setmediagalleryready() at simagegallery.js
 */
function setmediagallery_singleimage(){

    var confirmCallback = function(){
        var route = $('#mediaGalleryConfirmButton').data('route');
        var url = route+'?m='+$('.media-gallery-dialog .list-box.selected').data('media');
        /*at the loading might take a while, close media gallery first*/
        closesmodal('#media_gallery_modal');
        if ($('#media_gallery_modal').length>0)
            $('#media_gallery_modal').remove();  

        var modal = new Modal('#widget_modal_template');
        modal.onSingleImageAdd(url);        
    };
    
    setmediagallery_render(confirmCallback,'setmediagallery_singleimage');
}
function setmediagallery_multiimage(){
    var confirmCallback = function(){
        var route = $('#mediaGalleryConfirmButton').data('route');
        var url = route+'?m='+$('.media-gallery-dialog .list-box.selected').data('media');
        /*at the loading might take a while, close media gallery first*/
        closesmodal('#media_gallery_modal');
        if ($('#media_gallery_modal').length>0)
            $('#media_gallery_modal').remove();  

	var modal = new Modal('#widget_modal_template');
        modal.onMultiImageAdd(url);        
    };
    
    setmediagallery_render(confirmCallback,'setmediagallery_multiimage');
}
function setmediagallery_render(confirmCallback,refreshmethod){

    var cancelCallback = function(){
        closesmodal('#media_gallery_modal');
        $('.page-loader').hide();
	var modal = new Modal('#widget_modal_template');
	modal.show();
    };
    
    $('#media_gallery_modal .smodal').css({'z-index':'2000','top':0});/*set it higher than boostrap modal*/
    setmediagalleryready(confirmCallback,cancelCallback,refreshmethod);
}
/**
 * Save page layout settings
 */
function savepagelayout() {
    $('.page-loader').show();
    var container =  new Container('.workspace .canvas',false); //set width to false so not to load layout again
    var layout = container.saveLayout();

    $.ajax({
        url: $('.layouteditor-update').data('url'),
        data: $('#layout_editor_form').serialize()+'&layout='+JSON.stringify(layout),//get CSRF_TOKEN
        type: 'POST',
        dataType: 'json',
        cache: false,
        success: function(data) {
            if (data.status=='success') {
                window.location.href = data.redirect;
            }
            else {
                $('.page-loader').hide();     
                alert(data.message);
            }
        },
        error: function(XHR) {
            $(".page-loader").hide();   
            error(XHR);
        }
    });
    
}
/*
 * Init a container
 */
$( function() {
    new Container('.workspace .canvas',970);   
    /* turn on draggables */
    $('.row-draggable').draggable({
        helper: 'clone'
    });
    $('.col-draggable').draggable({
        helper: 'clone'
    });       
});
/*
 * Get ready theme switching for page layout editing
 */
$(document).ready(function () {
    $('#editing_theme').change(function(){
        var url = $(this).find(':selected').data('url');
        $('.page-loader').show();
        window.location.href = url;
    });
    $('#editing_page').change(function(){
        var url = $(this).find(':selected').data('url');
        $('.page-loader').show();
        window.location.href = url;
    });
});
$(window).scroll(function(){
    if ($(this).scrollTop() > 200) {//window scroll pass certain height
        $('.layout-editor .palette').addClass('fixed');
        $('.layout-editor .palette').css({'top':'100px'});
    } 
    else {
        $('.layout-editor .palette').removeClass('fixed');
        $('.layout-editor .palette').css({'top':'auto'});
    }
}); 