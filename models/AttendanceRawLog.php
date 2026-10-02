<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%attendance_raw_log}}".
 *
 * @property int $id
 * @property int|null $device_id
 * @property string $pin
 * @property string $scan_time
 * @property string $scan_date
 * @property int $status
 * @property int $in_out_mode
 * @property int $verify_mode
 * @property int $work_code
 * @property bool $is_processed
 * @property string $created_at
 */
class AttendanceRawLog extends ActiveRecord
{
    const MODE_CHECK_IN  = 0;
    const MODE_CHECK_OUT = 1;

    const VERIFY_FINGER = 1;
    const VERIFY_FACE   = 16;

    public static function tableName()
    {
        return '{{%attendance_raw_log}}';
    }

    public function rules()
    {
        return [
            [['pin', 'scan_time'], 'required'],
            [['device_id', 'status', 'in_out_mode', 'verify_mode', 'work_code'], 'integer'],
            [['scan_time', 'scan_date', 'created_at'], 'safe'],
            [['is_processed'], 'boolean'],
            [['pin'], 'string', 'max' => 30],
            [['pin', 'scan_time'], 'unique', 'targetAttribute' => ['pin', 'scan_time'], 'message' => 'Log scan sudah terdaftar.'],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (!empty($this->scan_time) && empty($this->scan_date)) {
                $this->scan_date = date('Y-m-d', strtotime($this->scan_time));
            }
            return true;
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_id' => 'Device ID',
            'pin' => 'PIN / Enrollment No',
            'scan_time' => 'Waktu Scan',
            'scan_date' => 'Tanggal Scan',
            'status' => 'Status',
            'in_out_mode' => 'In / Out Mode',
            'verify_mode' => 'Metode Verifikasi',
            'work_code' => 'Work Code',
            'is_processed' => 'Sudah Diproses',
            'created_at' => 'Tercatat Pada',
        ];
    }
}