<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\RenterCatChange */

$this->title = 'Update Renter Cat Change: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Renter Cat Changes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="renter-cat-change-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
