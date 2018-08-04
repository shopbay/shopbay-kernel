<?php
$this->widget('common.widgets.SDetailView', array(
    'data'=>$model,
    'columns'=>array(
        array(
            array('label'=>$model->getAttributeLabel('registered'),'type'=>'raw','value'=>$model->isRegistered?Sii::t('sii','Yes'):Sii::t('sii','No')),
            array('label'=>$model->getAttributeLabel('last_login_time'),'type'=>'raw','value'=>$model->isRegistered&&$model->account->last_login_time!=null?$model->formatDatetime($model->account->last_login_time,true):Sii::t('sii','not available')),
            array('name'=>'create_time','value'=>$model->formatDatetime($model->create_time,true)),
            array('name'=>'update_time','value'=>$model->formatDatetime($model->update_time,true)),
        ),
        array(
            array('label'=>$model->getAttributeLabel('first_name'),'type'=>'raw','value'=>$model->first_name!=null?$model->first_name:Sii::t('sii','unset')),
            array('label'=>$model->getAttributeLabel('last_name'),'type'=>'raw','value'=>$model->last_name!=null?$model->last_name:Sii::t('sii','unset')),
            array('label'=>$model->getAttributeLabel('gender'),'type'=>'raw','value'=>$model->gender!=null?Sii::t('sii',$model->gender):Sii::t('sii','unset')),
            array('label'=>$model->getAttributeLabel('birthday'),'type'=>'raw','value'=>$model->birthday!=null?$model->birthday:Sii::t('sii','unset')),
        ),
        array(
            array('label'=>$model->getAttributeLabel('email'),'type'=>'raw','value'=>$model->getEmail()),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('mobile'),'type'=>'raw','value'=>$model->addressData->mobile),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('address1'),'type'=>'raw','value'=>$model->addressData->address1.' '.$model->addressData->address2),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('postcode'),'type'=>'raw','value'=>$model->addressData->postcode),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('city'),'type'=>'raw','value'=>$model->addressData->city),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('state'),'type'=>'raw','value'=>$model->addressData->state),
            array('label'=>$this->getCustomerAddressForm()->getAttributeLabel('country'),'type'=>'raw','value'=>$model->addressData->country),
        ),
    ),
));
