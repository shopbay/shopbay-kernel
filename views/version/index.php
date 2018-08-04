<div class="version-footer">
    <?php   
        echo Sii::t('sii','Powered by {version}, Yii {yii1} and Yii {yii2}',[
                '{version}'=>Helper::getSystemVersion(app()->basepath),
                '{yii1}'=>Yii::getVersion(),
                '{yii2}'=>Yii::getYii2Version(),
            ]);
    ?>
</div>