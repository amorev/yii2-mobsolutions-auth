<?php

namespace Zvinger\Auth\Mobsolutions\migrations;

use yii\db\Migration;

/**
 * Class M171220074445Init
 */
class M171220074445Init extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%user_mobsol_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11),
            'app_id' => $this->string(64),
            'secret' => $this->string(64),
            'status' => $this->string(),
            'created_at' => $this->integer(11),
        ], $tableOptions);

        $this->createIndex('user_mobsol_token_app_id_uindex', '{{%user_mobsol_token}}', 'app_id', true);
        $this->createIndex('user_mobsol_token_secret_uindex', '{{%user_mobsol_token}}', 'secret', true);

        $this->addForeignKey('fk-user_mobsol_token-user_id', '{{%user_mobsol_token}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "M171220074445Init cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171220074445Init cannot be reverted.\n";

        return false;
    }
    */
}
