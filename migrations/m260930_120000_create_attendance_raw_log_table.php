<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%attendance_raw_log}}`.
 */
class m260930_120000_create_attendance_raw_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // Menggunakan utf8mb4 dan InnoDB
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%attendance_raw_log}}', [
            'id' => $this->bigPrimaryKey(),
            'device_id' => $this->integer()->null()->comment('ID mesin fingerprint jika ada multi-device'),
            'pin' => $this->string(30)->notNull()->comment('User ID/PIN pada mesin fingerprint'),
            'scan_time' => $this->dateTime()->notNull()->comment('Waktu scan absensi'),
            'scan_date' => $this->date()->notNull()->comment('Partisi tanggal untuk mempercepat query harian'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Status verifikasi: 1 = Berhasil'),
            'in_out_mode' => $this->smallInteger()->defaultValue(0)->comment('0 = Masuk, 1 = Keluar, dll'),
            'verify_mode' => $this->smallInteger()->defaultValue(1)->comment('1 = Fingerprint, 16 = Face/Password, dll'),
            'work_code' => $this->smallInteger()->defaultValue(0)->comment('Work code absensi'),
            'is_processed' => $this->boolean()->defaultValue(false)->comment('Flag apakah sudah dihitung ke tabel rekap harian'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Mencegah duplikasi data persis (PIN + Waktu Scan yang sama persis)
        $this->createIndex(
            'idx-unique-pin-scan_time',
            '{{%attendance_raw_log}}',
            ['pin', 'scan_time'],
            true
        );

        // Index untuk optimasi filter rekap per tanggal dan per PIN
        $this->createIndex(
            'idx-raw_log-pin-scan_date',
            '{{%attendance_raw_log}}',
            ['pin', 'scan_date']
        );

        // Index untuk background job / worker pemroses absensi
        $this->createIndex(
            'idx-raw_log-is_processed',
            '{{%attendance_raw_log}}',
            ['is_processed', 'scan_date']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%attendance_raw_log}}');
    }
}