<style>
.grid-view table.items td
{
    font-size: 1em;
    border: 0px #EDEDED solid;
    padding: 0.3em;
}
.grid-view table.items th
{
    color: #888888;
    border: 0px grey solid;
    background: white;
    text-align: center;
    height: 30px;
}
.grid-view table.items th a
{
    color: #888;
    font-weight: bold;
    text-decoration: none;
}
.grid-view table.items tr.even
{
    background: white;
}
.grid-view table.items tr.odd
{
    background: #F9F9F9;
}
.grid-view table.items tfoot tr
{
    background: white;
    border-collapse: collapse;
    width: 100%;
    border: 0px white solid;
    font-style:normal;
}

.grid-view table.items tfoot tr td
{
    border: 0px white solid;
}

.grid-view table.items a
{
    color: #888;
}
.grid-view table.items td.shipping
{
    border: 0px #EDEDED solid;
    vertical-align: top;
}
.grid-view table.subtotal td
{
    font-size: 1em;
    border: 0px #EDEDED solid;
    padding: 0.3em;
}
.grid-view table.subtotal td
{
    font-size: 1em;
    border: 0px #EDEDED solid;
    padding: 0.3em;
}
.grid-view .shop {
    float:left;
    color: grey;
    background: pink;
    border: 1px solid #EDEDED;
    border-bottom: 0px solid #EDEDED;
    padding:2px 10px;
    -webkit-border-top-right-radius: 5px;
    -webkit-border-top-left-radius: 5px;
    -moz-border-radius-topright: 5px;
    -moz-border-radius-topleft: 5px;
    border-top-right-radius: 5px;
    border-top-left-radius: 5px;
}
.grid-view table.imagename
{
    border-collapse: collapse;
    width: 100%;
    border: 0px #EDEDED solid;
    margin: 0px 0px;
}
.grid-view table.imagename td
{
    font-size: 1em;
    border: 0px #EDEDED solid;
    padding: 0.3em;
}

</style>
<div class="grid-view" style="padding: 15px 0px;">
    <?php 
        
        $this->renderPartial('common.modules.notifications.templates.email.order._order_header',array('model'=>$model));

        $this->renderPartial('common.modules.notifications.templates.email.order._order_shop',
                array('order'=>$model,
                      'shop_id'=>$model->shop_id,
                      'shippings'=>json_decode($model->item_shipping)));

        $this->renderPartial('common.modules.notifications.templates.email.order._order_footer',array('order'=>$model)); 
    ?>
</div>
