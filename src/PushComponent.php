<?php
namespace powerkernel\yiipush;

use yii\base\Component;
use yii\helpers\Url;
use powerkernel\yiipush\models\Notification;
use powerkernel\yiipush\models\UserToken;
use powerkernel\yiiuser\models\User;
/**
 * Class PushComponent
 * @package powerkernel\yiipush
 */
class PushComponent extends Component
{
    public $api_key;
    public function init()
    {
        parent::init();
    }
    public function getUuid($UserId="",$type=""){

        if(empty($type)){
             $Data = UserToken::find()->where(['user_id'=>(string)$UserId])->asArray()->all();
        }else{
             $Data = UserToken::find()->where(['user_id'=>(string)$UserId,'app_type'=>$type])->asArray()->all();
        }
       
        $UUIDS = array();
        if(!empty($Data)){          
            foreach($Data as $uuid){
                array_push($UUIDS,$uuid['uuid']);
            }
        }
        return $UUIDS;
    }
    public function remove($Id){
       $Notification = Notification::find()->where(['_id'=>(string)$Id,'user_id'=>(string)\Yii::$app->user->id])->one();    
       if(!empty($Notification) && $Notification->delete()){
            return[
                'success'=>true,
                'data'=>''
            ];
        }else{
            return[
                'success'=>false,
                'errors'=>'Unable to remove this notification, Please try after sometime.'
            ];
        }
    }
    public function send($input,$data){   
        
        $app_type               = !empty($input['app_type'])?$input['app_type']:"";      
        $Notification           = new Notification;
        $Notification->user_id  = !empty($input['user_id'])?(string)$input['user_id']:"";    
        $Notification->uuid     = $this->getUuid($Notification->user_id,$app_type); 
        $Notification->title    = !empty($input['title'])?$input['title']:"";
        $Notification->message  = !empty($input['message'])?$input['message']:"";
        $Notification->type     = !empty($input['type'])?$input['type']:"Notification";
        $Notification->order_id = !empty($data['order_id'])?(string)$data['order_id']:"";
        if ($Notification->validate()  && $Notification->save()){ 
            
            $user = User::find()->where(['_id'=>(string)$Notification->user_id])->one();
            if(count($Notification->uuid) > 0 && $user->notification_status=="on"){
               
                $msg = array(
                    'message'  =>$Notification->message,
                    'title'  => $Notification->title,
                    'body'  => $Notification->message,
                    "sound"=> "notifsound.mp3",
                    'vibrate'=> 1,
                    'vibration'  =>300 
                );    
                $time = $Notification->created_at;
                $fields = array
                (
                    'registration_ids' => $Notification->uuid,
                    'notification' => $msg,
                    'data'=>array(
                        'title'            =>   $Notification->title,
                        'message'          =>   $Notification->message,
                        '_id'              =>   (string)$Notification->_id,
                        'type'             =>   $Notification->type,
                        'order_id'         =>   (string)$Notification->order_id ,
                        'is_read'          =>   $Notification->is_read,
                        'created_at'       =>   $time,
                         "image"           => 'www/res/android/48x48.png',
                    ),
                    'priority'=> 'high',
                    'content_available'=> true,
                    'show_in_foreground'=>true,
                );
                $headers = array
                (
                    'Authorization: key=' . $this->api_key,
                    'Content-Type: application/json'
                );
                $ch = curl_init("https://fcm.googleapis.com/fcm/send");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $res = curl_exec($ch);
                curl_close($ch);

                $Notification->push_request  = $fields;
                $Notification->push_response = is_array(json_decode($res,true))?json_decode($res,true):$res;
            }else{
                $Notification->push_request  =  'UUID is not available or Notification status off';
                $Notification->push_response  = 'UUID is not available or Notification status off';
            }
            if($Notification->save()){
                return[
                    'success'=>true,
                    'data'=>$Notification
                ];
            }else{
                $Notification->validate();
                return[
                    'success'=>false,
                    'errors'=>$Notification->errors
                ];
            }
        }else{      
            $Notification->validate();          
            return[
                'success'=>false,
                'errors'=>$Notification->errors
            ];
        }
    }

}
