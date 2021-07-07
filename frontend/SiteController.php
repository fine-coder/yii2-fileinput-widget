<?php

namespace frontend\controllers;

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

        $documents = DocumentManager::find()->orderBy([
            'sort' => SORT_ASC
        ])->all();

        return $this->render('documents', compact('model', 'documents'));
    }
}
