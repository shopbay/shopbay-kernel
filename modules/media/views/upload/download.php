<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% var cnt = $('.files tr').length; %}
{% $('.files').parent().addClass('items'); %}
{% $('#description textarea').val(''); %}
{% $('.loading-gif').hide(); %}
{% $('#flash-bar').html(''); %}
{% $('.template-download td.error').html(''); %}
{% initmultimediafiles(); %}

{% for (var i=0, file; file=o.files[i]; i++) { %}
    {% var row="odd"; if (cnt%2==0) row="even"; %}
    <tr class="template-download fade {%=row%}">
        {% if (file.error) { %}
            {% $('#flash-bar').html(locale.fileupload.errors[file.errorFlash] || file.errorFlash); %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td width="5%" class="preview">
                {% if (file.thumbnail_url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}" style="vertical-align:middle;width:20px;height:20px"></a>
                {% } %}
            </td>
            <td width="60%" class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
                <span>{%=file.description%}</span>
            </td>
            <td width="30%" class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td width="5%" class="delete">
                {% var csrf = '&APP_CSRF_TOKEN='+$('.upload-form').find('input[type=hidden]').val(); %}
                <button title="Delete" style="padding:3px 2px;background:white;border:0;" class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url+csrf%}">
                    <i class="icon-trash icon-white"></i>
                    <?php echo Chtml::image($this->getController()->getAssetsUrl('common.assets.images').'/delete.png');?>
                </button>
            </td>         
        {% } %}

    </tr>
{% } %}
</script>
