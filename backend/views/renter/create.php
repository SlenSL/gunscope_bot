<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Renter */

$this->title = 'Create Renter';
$this->params['breadcrumbs'][] = ['label' => 'Renters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="renter-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
