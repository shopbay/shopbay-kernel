<div class="comment">
    <?php $author = $data->getAuthor(); ?>
    <table  width="100%" style="margin-bottom:0px;">
        <tr>
            <td class="avatar">
                 <?php echo $author->avatar;?>
            </td>
            <td style="padding-top:0px;vertical-align:top">
               <table width="100%">
                    <tr>
                        <td>
                            <span class="author"><?php echo CHtml::encode($author->name); ?></span>
                            <span class="time"><?php echo Helper::prettyDate($data->create_time);?></span>
                            <?php if ($data->rating!=null): ?>
                            <span class="rating">
                                 <?php $this->widget('CStarRating',
                                            array('name'=>'rating'.$data->id,
                                                  'readOnly'=>true,
                                                  'value'=>$data->rating)
                                        );
                                ?>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo Helper::addNofollow(Helper::purify(CHtml::decode($data->content))); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>       
</div>