<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\RequestNoreply */

$this->title = 'Create Request Noreply';
$this->params['breadcrumbs'][] = ['label' => 'Request Noreplies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="request-noreply-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
