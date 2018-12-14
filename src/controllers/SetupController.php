<?php
namespace powerkernel\yiipush\controllers;
use yii\filters\AccessControl;
use powerkernel\yiipush\models\UserToken;
/**
 * Class SetupController
 */
class SetupController extends \powerkernel\yiicommon\controllers\ActiveController
{
    public $modelClass = '';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            '__class' => AccessControl::class,
            'rules' => [
                [
                    'verbs' => ['OPTIONS'],
                    'allow' => true,
                ],
                [
                    'actions'=>['add-token','remove-token'],
                    'roles' => ['@'],
                    'allow' => true,
                ]
            ],
        ];
        return $behaviors;
    }  
    public function actionAddToken(){
        $model          = new UserToken;
        $model->user_id = (string)\Yii::$app->user->id;
        if($model->load(\Yii::$app->getRequest()->getParsedBody(),'')){
            $UserToken =  UserToken::find()->where(['user_id'=>(string)$model->user_id,'platform'=>$model->platform,'uuid'=>(string)$model->uuid,'app_type'=>$model->app_type])->one();
            if(empty($UserToken)){
                if($model->save()){
                    return[
                        'success'=>true,
                    ];
                }else{
                    $model->validate();
                    return[
                        'success'=>false,
                        'errors'=>$model->errors
                    ];
                }
            }else{
                return[
                    'success'=>true,
                ];
            }
        }else{
            return[
                'success'=>false,
                'errors'=>$model->errors
            ];
        }
    }
}
