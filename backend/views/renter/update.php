<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Renter */

$this->title = 'Update Renter: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Renters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="renter-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
