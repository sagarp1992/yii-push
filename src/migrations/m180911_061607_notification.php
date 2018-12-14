<?php

class m180911_061607_notification extends \yii\mongodb\Migration
{
    public function up()
    {
        $col = Yii::$app->mongodb->getCollection('notification');
    }

    public function down()
    {
        echo "m180911_061607_notification cannot be reverted.\n";

        return false;
    }
}
