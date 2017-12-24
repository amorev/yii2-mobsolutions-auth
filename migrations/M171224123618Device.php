<?php

namespace Zvinger\Auth\Mobsolutions\migrations;

use yii\db\Migration;

/**
 * Class M171224123618Device
 */
class M171224123618Device extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = NULL;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_mobile_push_device}}', [
            'id'            => $this->primaryKey(),
            'token_id'      => $this->integer(11),
            "deviceType"    => $this->string(255),
            "deviceId"      => $this->string(255),
            "deviceName"    => $this->string(255),
            "pushToken"     => $this->string(255),
            "deviceVersion" => $this->string(255),
            "appVersion"    => $this->string(255),
            'created_at'    => $this->integer(11),
        ], $tableOptions);

        $this->createIndex('user_mobile_push_device_token_id_deviceId_deviceType_uindex', '{{%user_mobile_push_device}}', ['token_id', 'deviceId', 'deviceType'], TRUE);
        $this->addForeignKey('fk-user_mobile_push_token-token_id', '{{%user_mobile_push_device}}', 'token_id', '{{%user_mobsol_token}}', 'id', 'cascade', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "M171224123618Device cannot be reverted . \n";

        return FALSE;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171224123618Device cannot be reverted . \n";

        return false;
    }
    */
}
