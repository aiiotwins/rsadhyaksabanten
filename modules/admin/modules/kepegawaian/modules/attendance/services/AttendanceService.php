<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\services;

use Yii;
use DateTime;
use yii\db\Query;
use app\components\ShiftDetector;
use app\modules\admin\modules\kepegawaian\modules\attendance\models\AttendanceRawLog;

class AttendanceService
{
    public function processDates(array $dates, $minGapMinutes = 15)
    {
        if (empty($dates)) {
            return 0;
        }

        // Query mengambil MIN dan MAX scan time per device_id, pin, dan scan_date
        $records = (new Query())
            ->select([
                'device_id',
                'pin',
                'scan_date',
                'first_scan' => 'MIN(scan_time)',
                'last_scan'  => 'MAX(scan_time)',
                'total_taps' => 'COUNT(id)'
            ])
            ->from('{{%attendance_raw_log}}')
            ->where(['scan_date' => $dates])
            ->groupBy(['device_id', 'pin', 'scan_date']) // Disertakan device_id agar kalkulasi valid per device
            ->all();

        $processedCount = 0;
        $minGapSeconds = $minGapMinutes * 60;

        foreach ($records as $row) {
            $shiftDetector = ShiftDetector::detect($row['first_scan'], $row['last_scan']);
            $clockIn = $row['first_scan'];
            $clockOut = null;
            $status = 'INCOMPLETE';

            $timeIn = strtotime($row['first_scan']);
            $timeOut = strtotime($row['last_scan']);
            
            // Validasi jarak jam pulang minimal 15 menit dari jam masuk
            if (($timeOut - $timeIn) >= $minGapSeconds) {
                $clockOut = $row['last_scan'];
                $status = 'PRESENT';
            }

            // UPSERT: Jika PIN + Date + device_id sudah ada, lakukan UPDATE (bukan INSERT ganda)
            Yii::$app->db->createCommand()->upsert('{{%attendance_daily_summary}}', [
                'device_id'  => $row['device_id'],
                'pin'        => $row['pin'],
                'date'       => $row['scan_date'],
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'total_taps' => (int)$row['total_taps'],
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'shift_code' => $shiftDetector['shift']['code'],
                'scheduled_in' => (new DateTime($shiftDetector['shift']['start']))->format('H:i:s'),
                'scheduled_out' => (new DateTime($shiftDetector['shift']['end']))->format('H:i:s'),
                'late_minutes' => ($shiftDetector['kedatangan']['status'] == "TERLAMBAT") ? $shiftDetector['kedatangan']['selisih_menit'] : null,
                'early_leave_minutes' => ($status == 'INCOMPLETE') ? null : (($shiftDetector['kepulangan']['status'] == "PULANG_CEPAT") ? $shiftDetector['kepulangan']['selisih_menit'] : null),
            ], [
                // Kolom yang di-update jika kombinasi device_id + pin + date sudah ada
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'total_taps' => (int)$row['total_taps'],
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'shift_code' => $shiftDetector['shift']['code'],
                'scheduled_in' => (new DateTime($shiftDetector['shift']['start']))->format('H:i:s'),
                'scheduled_out' => (new DateTime($shiftDetector['shift']['end']))->format('H:i:s'),
                'late_minutes' => ($shiftDetector['kedatangan']['status'] == "TERLAMBAT") ? $shiftDetector['kedatangan']['selisih_menit'] : null,
                'early_leave_minutes' => ($shiftDetector['kepulangan']['status'] == "PULANG_CEPAT") ? $shiftDetector['kepulangan']['selisih_menit'] : null,
            ])->execute();

            $processedCount++;
        }

        AttendanceRawLog::updateAll(
            ['is_processed' => 1],
            ['scan_date' => $dates, 'is_processed' => 0]
        );

        return $processedCount;
    }
}