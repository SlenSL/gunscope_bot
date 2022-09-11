<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Renter */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="renter-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'place_num')->textInput() ?>

    <?= $form->field($model, 'category')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'chat_id')->textInput() ?>

    <?= Html::tag('a', 'Генерировать рандомно', ['onclick' => 'generateRandomCode()', 'style' => 'cursor: pointer']) ?>
    <?= $form->field($model, 'verify_code')->textInput() ?>
    
    <?= $form->field($model, 'verify_status')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
