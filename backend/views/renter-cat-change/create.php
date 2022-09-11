<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\RenterCatChange */

$this->title = 'Create Renter Cat Change';
$this->params['breadcrumbs'][] = ['label' => 'Renter Cat Changes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="renter-cat-change-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
