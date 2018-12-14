<?php
namespace powerkernel\yiipush\controllers;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use powerkernel\yiipush\models\Notification;
class NotificationController extends \powerkernel\yiicommon\controllers\ActiveController
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
                    'actions' => [ 'delete', 'index','view','enable','badge','remove','test','clear-all'],
                    'roles' => ['@'],
                    'allow' => true,
                ],  
            ],
        ];
        return $behaviors;
    }   
    protected function verbs()
    {
        $parents = parent::verbs();
        return array_merge(
            $parents,
            [
                'index' => ['GET'],
                'delete' => ['POST'],
                'view' => ['POST'],
            ]
        );
    }
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];        
        return $actions;
    }
    public function prepareDataProvider()
    {

        Notification::updateAll([
            'is_read' => 'Y',
        ], ['is_read'=>'N','user_id' => (string)\Yii::$app->user->id]);

        $query = Notification::find()->select(['title','message','type','is_read','created_at','order_id'])
       ->where(['user_id'=>(string)\Yii::$app->user->id])
        ->orderBy([
            'created_at'=>SORT_DESC
        ]);;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $dataProvider;
    }
    public function actionRemove($id){
        return \Yii::$app->push->remove($id);
    }
    public function actionBadge(){
        $Count = Notification::find()->where(['user_id' => (string)\Yii::$app->user->id,'is_read'=>'N'])->count();
        return[
            'success'=>true,
            'data'=>['badge'=>$Count]
        ];
    }
    public function actionEnable(){        
        $me = \Yii::$app->user->identity;
        $me->load(\Yii::$app->getRequest()->getParsedBody(), '');
        if ($me->save(true, ['notification_status'])) {
            return [
                'success' => true,
                'data' => $me->notification_status
            ];
        } else {
            return [
                'success' => false,
                'errors' => $me->errors
            ];
        }
    }
    public function actionTest(){
        $input = array(
            'user_id'=>\Yii::$app->user->id,
            'title'  =>'Test Notification Title '.\Yii::$app->name,
            'message'=>'Test Notification Message ',
            'type'=>'Order'
        );
        return \Yii::$app->push->send($input,array('order_number'=>(string)234234234234,'order_id'=>234234234234)); 
    }
    public function actionClearAll(){
        Notification::deleteAll(['user_id' => (string)\Yii::$app->user->id]);
        return [
            'success'=>true,
            'data'=>'',
            'message'=>''
        ];
    }

}
