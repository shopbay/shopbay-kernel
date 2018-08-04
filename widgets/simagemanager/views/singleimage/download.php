<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
        <div class="template-download fade">
        {% if (file.error) { %}
            {% $('#flash-bar').html(locale.fileupload.errors[file.error] || file.error); %}
        {% } else { %}
             <div class="delete">
                <img onload="javascript:insertsingleimage();" src="{%=file.thumbnail_url%}">
                <button id="delete-button" title="Delete" class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="fa fa-times"></i>
                </button>
            </div>     
        {% } %}
        </div>
{% } %}
{% $('#SingleImageForm-form').bind('fileuploaddestroyed', function() {restoresingleimage();}); %}
</script>
