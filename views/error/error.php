<div class="segment error">

    <h1><?php echo Sii::t('sii','Error {code}',['{code}'=>$code]);?></h1>

    <?php foreach ($messages as $message) {
            echo CHtml::tag('p', [], $message);
          }
    ?>

</div>
