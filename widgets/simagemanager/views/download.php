<script id="template-download" type="text/x-tmpl">
{% initmultiimages(); %}
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            {% $('#flash-bar').html(locale.fileupload.errors[file.error] || file.error); %}
        {% } else { %}
            <td width="3%" class="preview">
                <input type="radio" name="primaryimage" value="{%=file.filename%}">
            </td>
            <td width="25%" class="preview">
                {% if (file.thumbnail_url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}">
                        <img src="{%=file.thumbnail_url%}" style="width:100px;height:100px">
                    </a>
                {% } %}
            </td>
            <td width="70%" class="name">
                <div class="wordwrap">
                    <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
                    <br><br>{%=o.formatFileSize(file.size)%}
                </div>
            </td>
            <td width="5%" class="delete">
                <button title="Delete" class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i style="cursor:pointer" class="fa fa-times"></i>    
                </button>
            </td>    
        {% } %}
    </tr>
{% } %}
</script>

