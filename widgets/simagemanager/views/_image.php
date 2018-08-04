<tr class="template-download fade">
    <td width="3%" class="preview">
        <input type="radio" name="primaryimage" value="<?php echo $image->filename;?>" <?php echo $image->primary?'checked':'';?>>
    </td>
    <td width="25%" class="preview">
        <a href="<?php echo $image->url;?>" title="<?php echo $image->name;?>" rel="gallery" download="<?php echo $image->name;?>">
            <img src="<?php echo $image->thumbnail_url;?>" width="<?php echo $imageWidth;?>" height="<?php echo $imageHeight;?>">
        </a>
    </td>
    <td width="70%" class="name">
        <div class="wordwrap">
            <?php if ($image->name==Image::EXTERNAL_IMAGE):?>
                <a href="<?php echo $image->url;?>" title="<?php echo $image->url;?>" rel="<?php echo $image->url;?>&&'gallery'" download="<?php echo $image->name;?>"><?php echo $image->url;?></a>
            <?php else:?>
                <a href="<?php echo $image->url;?>" title="<?php echo $image->name;?>" rel="<?php echo $image->name;?>&&'gallery'" download="<?php echo $image->name;?>"><?php echo $image->name;?></a>
                <br><br><?php echo Helper::formatBytes($image->size);?>
            <?php endif;?>
        </div>
    </td>
    <td width="5%" class="delete">
        <button class="btn btn-danger" data-type="<?php echo $image->delete_type;?>" data-url="<?php echo $image->delete_url;?>">
            <i class="icon-trash icon-white"></i>
            <i style="cursor:pointer" class="fa fa-times"></i>    
        </button>
    </td>         
</tr>