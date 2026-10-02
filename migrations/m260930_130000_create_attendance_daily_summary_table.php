<?php

use yii\db\Migration;

class m260930_130000_create_attendance_daily_summary_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%attendance_daily_summary}}', [
            'id' => $this->bigPrimaryKey(),
            'pin' => $this->string(30)->notNull(),
            'date' => $this->date()->notNull(),
            'clock_in' => $this->dateTime()->null(),
            'clock_out' => $this->dateTime()->null(),
            'total_taps' => $this->integer()->defaultValue(0),
            'status' => $this->string(20)->defaultValue('PRESENT')->comment('PRESENT, INCOMPLETE, ABSENT'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Kunci unik: 1 record per PIN per Hari
        $this->createIndex(
            'idx-unique-pin-date',
            '{{%attendance_daily_summary}}',
            ['pin', 'date'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%attendance_daily_summary}}');
    }
}