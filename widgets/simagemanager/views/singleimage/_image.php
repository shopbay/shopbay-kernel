<div class="template-download fade in">
    <div class="delete">
        <img onload="javascript:insertsingleimage();" src="<?php echo $image->url;?>">
        <button id="delete-button" title="Delete" class="btn btn-danger" data-type="<?php echo $image->delete_type;?>" data-url="<?php echo $image->delete_url;?>">
            <i class="fa fa-times"></i>
        </button>
    </div>     
</div>
