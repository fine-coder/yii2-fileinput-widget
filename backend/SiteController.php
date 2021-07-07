<?php

namespace backend\controllers;

use common\models\Documents;
use common\models\DocumentManager;

// ...

/**
 * Site controller
 */
class SiteController extends Controller
{
    // ...

    public function actionDocuments()
    {
        $model = Documents::findOne(['id' => 1]);

        Yii::$app->cache->flush(); // Виджет FileInput кэшируется, возможно этот код решит проблему, которая иногда возникает

        $post = Yii::$app->request->post();

        if ($model->load($post)) {
            // -------------------------------------------------
            // Сохранение заголовков изображений

            $caption = $post['Documents']['fileCaption'];
            $caption2 = $post['Documents']['fileCaption2']; // Особенность сохранения заголовков изображений в виджете FileInput: https://plugins.krajee.com/file-input-ajax-demo/7

            $name = $post['Documents']['fileName'];

            if (isset($name) && count($name) > 0) {
                for ($i = 0; $i < count($name); $i++) {
                    $document = DocumentManager::find()->where(['item_id' => 1])->andWhere(['name' => $name[$i]])->one();

                    if ($caption[$i] == '') {
                        $caption[$i] = $caption2[$i];
                    }

                    if ($document) {
                        $document->title = $caption[$i];
                        $document->save();
                    } else {
                        // Только добавленные изображения имеют в БД title = NULL
                        // Только добавленные изображения мы не можем перетащить в другое место
                        $document = DocumentManager::find()->where(['item_id' => 1])->andWhere(['title' => null])->one();

                        if ($document) {
                            $document->title = $caption[$i];
                            $document->save();
                        }
                    }
                }
            }

            // -------------------------------------------------

            if ($model->save()) {
                Yii::$app->session->addFlash('success', 'Сохранено');

                return $this->refresh();
            }
        }

        return $this->render('documents', [
            'model' => $model,
        ]);
    }

    public function actionDeleteOneImage($class, $id, $image)
    {
        if (Yii::$app->request->isAjax) {
            $model = '';
            $folder = '';

            if ($class == "Documents") {
                $model = Documents::findOne(['id' => $id]);
                $folder = 'documents';
            }

            if ($model != '' && $folder != '' && $image != '') {
                $path = Yii::getAlias('@uploads') . '/' . $folder . '/' . $image;

                if (file_exists($path)) {
                    FileHelper::unlink($path);
                }

                $model->image = '';
                $model->save();
            }

            return true;
        }

        throw new MethodNotAllowedHttpException();
    }

    public function actionDeleteDocument()
    {
        if (($model = DocumentManager::findOne(Yii::$app->request->post('key'))) and $model->delete()) {
            $file = Yii::getAlias('@uploads') . '/documents/' . $model->name;

            if (file_exists($file)) {
                FileHelper::unlink($file);
            }

            return true;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не существует');
        }
    }

    public function actionSortDocument($id)
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post('sort');

            if ($post['oldIndex'] > $post['newIndex']) {
                $param = ['and', ['>=', 'sort', $post['newIndex']], ['<', 'sort', $post['oldIndex']]];
                $counter = 1;
            } else {
                $param = ['and', ['<=', 'sort', $post['newIndex']], ['>', 'sort', $post['oldIndex']]];
                $counter = -1;
            }

            DocumentManager::updateAllCounters(['sort' => $counter], [
                'and',
                ['item_id' => $id],
                $param
            ]);

            DocumentManager::updateAll(['sort' => $post['newIndex']], [
                'id' => $post['stack'][$post['newIndex']]['key']
            ]);

            return true;
        }

        throw new MethodNotAllowedHttpException();
    }

    public function actionSaveDocument()
    {
        $this->enableCsrfValidation = false;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $dir = Yii::getAlias('@uploads') . '/documents/';

            if (!file_exists($dir)) {
                FileHelper::createDirectory($dir);
            }

            $files = UploadedFile::getInstancesByName('Documents[docFiles]');

            if (count($files) > 0) {
                // Во вьюшке documents поставили uploadAsync = false, чтобы при сохранении заголовки соответствовали нужным файлам
                // Этот экшн будет вызван только один раз для всех загружаемых файлов, поэтому используем foreach вместо $file[0]
                foreach ($files as $file) {
                    $model = new DocumentManager();
                    $model->name = strtotime('now') . '_' . Yii::$app->getSecurity()->generateRandomString(10) . '.' . $file->extension;
                    $model->load($post);
                    $model->validate();

                    // Если валидация прошла не успешно, метод hasErrors будет возвращать ошибки
                    if ($model->hasErrors()) {
                        $result = [
                            'error' => $model->getFirstError('Documents[docFiles]')
                        ];
                    } else {
                        if ($file->saveAs($dir . $model->name)) {
                            $result = [
                                'filelink' => str_replace('admin/', '', Url::home(true)) . 'uploads/documents/' . $model->name,
                                'filename' => $model->name
                            ];

                            $model->save();
                        } else {
                            $result = [
                                'error' => 'Ошибка'
                            ];
                        }
                    }
                }
            } else {
                $result = [
                    'error' => 'Не выбраны файлы для загрузки'
                ];
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return $result;
        } else {
            throw new BadRequestHttpException('Only POST is allowed');
        }
    }
}
