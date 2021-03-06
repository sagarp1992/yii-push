<?php
namespace powerkernel\yiipush\models;
use powerkernel\yiicommon\behaviors\DefaultDateTimeBehavior;
use powerkernel\yiiuser\models\User;
use Yii;
class Notification extends \yii\mongodb\ActiveRecord
{

    public static function collectionName()
    {
        return 'notification';
    }
    public function attributes()
    {
        return [
            '_id',
            'title',
            'message',
            'user_id',
            'uuid',
            'is_read',
            'order_id',
            'type',
            'push_request',
            'push_response',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ];
    }
    public function rules()
    {
        return [
            [['title', 'message','user_id'], 'required'],
            [['message'], 'string'],
            [['title'], 'string'],
            ['type', 'default', 'value' => "Notification"],
            ['is_read', 'default', 'value' =>"N"],
            ['is_read', 'in', 'range' => ['N','Y']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => '_id']],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();       
        return $fields;
    }


    public function behaviors()
    {
        return [
            DefaultDateTimeBehavior::class,
        ];
    }
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_by = (string)Yii::$app->user->id;

        }

        $this->updated_by = (string)Yii::$app->user->id;
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub

    }
}
