<?php
namespace powerkernel\yiipush\controllers;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use powerkernel\yiipush\models\Notification;
class AdminController extends \powerkernel\yiicommon\controllers\ActiveController
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
                    'actions' => ['send-notification'],
                    'roles' => ['admin'],
                    'allow' => true,
                ],  
            ],
        ];
        return $behaviors;
    }  
    public function actionSendNotification(){
        $Data               = \Yii::$app->getRequest()->getParsedBody();
        $model              =  new Notification();
        $model->user_id     = \Yii::$app->user->id;
        $model->title       = $Data['title'];
        $model->message     = $Data['message'];
        if(!$model->validate()){
            return[
                'success'=>false,
                'errors'=>$model->errors
            ];
        }
        if(!empty($Data['userType']) && !empty($Data['userList'])  && !empty($Data['message']) && !empty($Data['title'])){
            
                foreach($Data['userList'] as $userId){
                    $input = array(
                        'user_id'=>(string)$userId['value'],
                        'title'  => $Data['title'].' '.\Yii::$app->name,
                        'message'=> $Data['message'],
                        'type'=>'Admin',
                        'app_type'=>$Data['userType']
                    ); 
                   \Yii::$app->push->send($input,array()); 
                }
                return[
                    'success'=>true
                ];
                
        }else{
            return[
                'success'=>false,
                'errors'=>'Parameters are missing'
            ];
        }
    }
}
