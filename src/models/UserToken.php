<?php
namespace powerkernel\yiipush\models;
use powerkernel\yiicommon\behaviors\UTCDateTimeBehavior;
use powerkernel\yiicommon\Core;
use Yii;
use yii\helpers\Markdown;

class UserToken extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'user_token';
    }
    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'platform',
            'app_type'      ,      
            'uuid',
            'created_at',
            'updated_at'
        ];
    }
    public function rules()
    {
        return [            
            [['user_id','uuid','platform','app_type'], 'required'],
            [['platform'], 'in', 'range' => ['Android','Ios']],
            [['app_type'], 'in', 'range' => ['Driver','Owner','User']],
        ];
    }
    public function behaviors()
    {
        return [
            UTCDateTimeBehavior::class,
        ];
    }

}
