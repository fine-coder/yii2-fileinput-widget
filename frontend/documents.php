<?php

/* @var $this yii\web\View */
/* @var $model common\models\Documents */
/* @var $documents common\models\DocumentManager */

$this->title = 'Документы';

//$this->registerMetaTag(['name' => 'description', 'content' => '']);

$this->registerCssFile('@web/assets/fancybox/jquery.fancybox.min.css');

$this->registerJsFile('@web/assets/fancybox/jquery.fancybox.min.js', [
    'depends' => [
        'yii\web\YiiAsset'
    ]
]);

?>

<div class="documents">
    <h1 class="text-center">
        <?= \yii\helpers\Html::encode($this->title) ?>
    </h1>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h2>Учредительные документы</h2>

            <?php foreach ($documents as $document): ?>
            <div>
                <a class="link" href="/uploads/documents/<?= $document->name ?>" target="_blank">
                    <?php
                    $name = explode('.', $document->name);
                    $ext = mb_strtolower(end($name));

                    if ($ext == 'doc' || $ext == 'docx') {
                        $icon = '<i class="fa fa-file-word-o text-primary"></i>';
                    } elseif ($ext == 'xls' || $ext == 'xlsx') {
                        $icon = '<i class="fa fa-file-excel-o text-success"></i>';
                    } elseif ($ext == 'pdf') {
                        $icon = '<i class="fa fa-file-pdf-o text-danger"></i>';
                    } elseif ($ext == 'zip') {
                        $icon = '<i class="fa fa-file-archive-o text-muted"></i>';
                    } elseif ($ext == 'txt') {
                        $icon = '<i class="fa fa-file-text-o text-info"></i>';
                    } elseif ($ext == 'jpg' || $ext == 'jpeg') {
                        $icon = '<i class="fa fa-file-image-o text-danger"></i>';
                    } elseif ($ext == 'png') {
                        $icon = '<i class="fa fa-file-image-o text-primary"></i>';
                    } else {
                        $icon = '<i class="fa fa-file-o"></i>';
                    }
                    ?>

                    <?= $icon ?>
                    <span><?= $document->title ?></span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="main-img">
                <div style="background-image:url('/uploads/documents/main.jpg')"></div>
            </div>
        </div>
    </div>

    <?php
    if ($model->image == 'no_image.jpg') {
        $image = 'images/';
    } else {
        $image = 'documents/';
    }

    $image .= $model->image;
    ?>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <a href="/uploads/<?= $image ?>" data-fancybox="document">
                <div class="image">
                    <img src="/uploads/<?= $image ?>" alt="">
                    <div class="loupe"></div>
                </div>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="text">
                <?= $model->text ?>
            </div>
        </div>
    </div>

</div>

<?php
// http://www.webmasterhelp.ru/jquery/podklyuchenie-i-nastrojka-fancybox-3/
$script = <<<EOD
$('[data-fancybox="document"]').fancybox({
    animationEffect: 'zoom-in-out', // Возможные значения: zoom, fade, zoom-in-out
});
EOD;

$this->registerJs($script);
?>
