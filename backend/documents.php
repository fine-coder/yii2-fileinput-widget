<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use vova07\imperavi\Widget;
use kartik\file\FileInput;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model common\models\Documents */

$this->title = 'Документы';

$temp_id = (int)substr(strtotime('now'), -8); // Формируем некий уникальный item_id, которого не будет в document_manager
$temp_id = -$temp_id;

?>

<h1 class="text-center">
    <?= Html::encode($this->title) ?>
</h1>

<div class="documents-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <div class="row">

        <?php
        if ($model->image == '') {
            $preview = '';
            $preview_caption = '';
            $preview_size = '';
        } elseif ($model->image == 'no_image.jpg') {
            $preview = str_replace('admin/', '', Url::home(true)) . 'uploads/images/' . $model->image;
            $preview_caption = $model->image;
            $preview_size = '';
        } else {
            $preview = str_replace('admin/', '', Url::home(true)) . 'uploads/documents/' . $model->image;
            $preview_caption = $model->image;
            $preview_size = filesize(Yii::getAlias('@uploads') . '/documents/' . $model->image);
        }
        ?>

        <?= $form->field($model, 'file', ['options' => ['class' => 'col-lg-6 col-md-6 col-sm-12']])->widget(FileInput::class, [
            'language' => 'ru',
            'pluginOptions' => [
                'deleteUrl' => Url::toRoute(['/site/delete-one-image', 'class' => $model->formName(), 'id' => $model->id, 'image' => $model->image]),
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'browseClass' => 'btn btn-primary btn-block',
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' =>  'Выбрать изображение',
                'initialPreview' => $preview,
                'initialPreviewAsData' => true,
                'initialPreviewConfig' => [
                    ['caption' => $preview_caption, 'size' => $preview_size],
                ],
            ],
            'options' => ['accept' => 'image/*'],
        ]) ?>

        <?= $form->field($model, 'text', ['options' => ['class' => 'col-lg-6 col-md-6 col-sm-12']])->widget(Widget::class, [
            'settings' => [
                'lang' => 'ru',
                'minHeight' => 305,
                'formatting' => ['p', 'blockquote', 'h2', 'h1'],
                'replaceDivs' => false,
                'deniedTags' => false,
                //'allowedTags' => ['p', 'h1', 'h2', 'h3', 'h4', 'a', 'img', 'span', 'div'], // Список тэгов, доступных для вставки в редактор
                //'cleanOnPaste' => false,
                //'convertImageLinks' => false,
                'pastePlainText' => true,
                //'paragraphize' => false,
                //'uploadOnlyImage' => false,
                //'validatorOptions' => ['maxSize' => 200000000], // Примерно 200 мб
                //'imageUpload' => Url::to(['/site/save-redactor-img', 'sub' => 'documents']),
                //'fileUpload' => Url::to(['/site/save-redactor-file']),
                'plugins' => [
                    'clips',
                    'fullscreen'
                ],
                'clips' => [
                    ['Lorem ipsum...', 'Lorem...'],
                    ['red', '<span class="label-red">red</span>'],
                    ['green', '<span class="label-green">green</span>'],
                    ['blue', '<span class="label-blue">blue</span>']
                ]
            ]
        ]); ?>

        <?php /* $form->field($model, 'title', ['options' => ['class' => 'col-xs-6']])->textInput(['maxlength' => true]) */ ?>

    </div>

    <?= $form->field($model, 'temp_id', ['options' => ['class' => 'hidden']])->hiddenInput(['value' => $temp_id])->label(false) ?>

    <?= $form->field($model, 'docFiles[]')->widget(FileInput::class, [
        'language' => 'ru',
        //'name' => 'docFiles[]',
        'options' => [
            'multiple' => true,
            //'accept' => 'image/*'
        ],
        'pluginOptions' => [
            'theme' => "explorer",
            //'initialPreviewShowDelete' => false,
            'showUpload' => false,
            'showRemove' => false,
            'deleteUrl' => Url::toRoute(['/site/delete-document']),
            'initialPreview' => $model->filesLink, // Приходит массив с url файлов
            'initialPreviewAsData' => true,
            'overwriteInitial' => false,
            'initialPreviewConfig' => $model->filesLinkData,
            'uploadAsync' => false, // Важно: когда имеем дело с заголовками, то ставим uploadAsync = false, чтобы при сохранении каждый заголовок соответствовал нужному файлу, экшн '/site/save-document' будет вызван только 1 раз для всех загружаемых файлов, в нем сохраняем файлы через foreach. Если сохранение заголовков не требуется, то можем поставить uploadAsync = true, экшн '/site/save-document' будет вызван для каждого загружаемого файла, foreach не нужен, сохраняем файл через $file[0]->saveAs.
            'uploadUrl' => Url::to(['/site/save-document']),
            'uploadExtraData' => [
                //'DocumentManager[class]' => 'documents', //$model->formName(),
                'DocumentManager[item_id]' => ($model->id != '' ? $model->id : $temp_id), //(isset($model->id) ? $model->id : 0)
                //'DocumentManager[title]' => ($model->id != '' ? '' : $temp_id)
            ],

            'fileActionSettings' => [
                'showZoom' => true,
                'showRemove' => false,
                'showUpload' => false
            ],

            //'initialPreviewFileType'=> 'pdf',
            //'previewFileType' => 'any',
            //'allowedPreviewTypes'=> ['image', 'text'],
            'allowedPreviewTypes' => null,
            'preferIconicPreview' => true, // this will force thumbnails to display icons for following file extensions
            'previewFileIcon' => '<i class="fa fa-file-o"></i>',
            'previewFileIconSettings' => [
                'doc' => '<i class="fa fa-file-word-o text-primary"></i>',
                'xls' => '<i class="fa fa-file-excel-o text-success"></i>',
                'ppt' => '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                'pdf' => '<i class="fa fa-file-pdf-o text-danger"></i>',
                'zip' => '<i class="fa fa-file-archive-o text-muted"></i>',
                'htm' => '<i class="fa fa-file-code-o text-info"></i>',
                'txt' => '<i class="fa fa-file-text-o text-info"></i>',
                'mov' => '<i class="fa fa-file-video-o text-warning"></i>',
                'mp3' => '<i class="fa fa-file-audio-o text-warning"></i>',

                'jpg' => '<i class="fa fa-file-image-o text-danger"></i>',
                'gif' => '<i class="fa fa-file-image-o text-muted"></i>',
                'png' => '<i class="fa fa-file-image-o text-primary"></i>'
            ],

            'previewFileExtSettings' => [
                'doc' => new JsExpression('function(ext) {
                    return ext.match(/(doc|docx)$/i);
                }'),
                'xls' => new JsExpression('function(ext) {
                    return ext.match(/(xls|xlsx)$/i);
                }'),
                'ppt' => new JsExpression('function(ext) {
                    return ext.match(/(ppt|pptx)$/i);
                }'),
                'zip' => new JsExpression('function(ext) {
                    return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
                }'),
                'htm' => new JsExpression('function(ext) {
                    return ext.match(/(htm|html)$/i);
                }'),
                'txt' => new JsExpression('function(ext) {
                    return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
                }'),
                'mov' => new JsExpression('function(ext) {
                    return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
                }'),
                'mp3' => new JsExpression('function(ext) {
                    return ext.match(/(mp3|wav)$/i);
                }')
            ],

            //'maxFileCount' => 10,
            'previewThumbTags' => [
                '{name}' => '',
                '{title}' => '',
                '{TAG_CSS_NEW}' => '', // new thumbnail input
                '{TAG_CSS_INIT}' => 'kv-hidden' // hide the initial input
            ],
            'initialPreviewThumbTags' => $model->thumbTags,

            // Ниже создаем два <input type="text"> - это особенность сохранения заголовков изображений в виджете FileInput: https://plugins.krajee.com/file-input-ajax-demo/7
            'layoutTemplates' => [
                'footer' =>
                    '<div class="file-thumbnail-footer">
                        <div class="file-footer-caption">
                            <div class="file-caption-info">
                                <input name="' . $model->formName() . '[fileCaption][]" class="kv-input kv-new form-control input-sm form-control-sm text-center {TAG_CSS_INIT}" value="{title}" placeholder="Введите название...">
                                <input name="' . $model->formName() . '[fileCaption2][]" class="kv-input kv-new form-control input-sm form-control-sm text-center {TAG_CSS_NEW}" value="{caption}" placeholder="Введите название...">
                                <input type="hidden" name="' . $model->formName() . '[fileName][]" value="{name}">
                            </div>
                        </div>
                        {progress}{indicator}{actions}
                    </div>',
                'actionZoom' => '<a href="/uploads/documents/{name}" class="{zoomClass}" title="Открыть файл" target="_blank">{zoomIcon}</a>'
            ],
        ],
        'pluginEvents' => [
            'filebatchselected' => 'function(event, files) {
                $(this).fileinput("upload");
            }',
            /*'fileuploaded' => 'function(event, previewId, index, fileId){
                console.log(\'File uploaded\', previewId, index, fileId);
            }',*/
            /*'fileuploaded' => new \yii\web\JsExpression('function(event, data, previewId, index, fileId) {
                console.log(previewId);
            }'),*/
            // когда перетащили элемент у нас будет вызываться функция, которая будет запускать пост-запрос на /site/sort-document
            'filesorted' => new \yii\web\JsExpression('function(event, params) {
                $.post("'.\yii\helpers\Url::toRoute(["/site/sort-document", "id" => $model->id]).'", {sort: params});
            }')
        ]
    ]) ?>

    <div class="form-group text-center">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = "
setTimeout(function() {
    $('.alert-success').slideUp();
}, 1200);
";

$this->registerJs($script);
?>
