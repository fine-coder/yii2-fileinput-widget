<?php

namespace common\models;

use yii\helpers\Url;

/**
 * This is the model class for table "document_manager".
 *
 * @property int $id
 * @property string $name
 * @property int $item_id
 * @property string|null $title
 * @property int $sort
 */
class DocumentManager extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_manager';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'item_id'], 'required'],
            [['item_id', 'sort'], 'integer'],
            [['sort'], 'default', 'value' => function ($model) {
                    $count = DocumentManager::find()->count();
                    // каждая новая картинка будет иметь сорт на единицу больше предыдущей
                    return ($count > 0) ? $count++ : 0;
                }
            ],
            [['name', 'title'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'item_id' => 'Item ID',
            'title' => 'Title',
            'sort' => 'Sort',
        ];
    }

    public function getFileUrl()
    {
        if ($this->name) {
            $path = str_replace('admin/', '', Url::home(true)) . 'uploads/documents/' . $this->name;
        } else {
            $path = str_replace('admin/', '', Url::home(true)) . 'uploads/images/no_image.jpg';
        }

        return $path;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            DocumentManager::updateAllCounters(['sort' => -1], ['and', ['item_id' => $this->item_id], ['>', 'sort', $this->sort]]);
            return true;
        } else {
            return false;
        }
    }
}
