<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\services;

use Yii;
use yii\db\Query;
use app\modules\admin\modules\kepegawaian\modules\attendance\models\AttendanceRawLog;

class AttendanceService
{
    public function processDates(array $dates, $minGapMinutes = 15)
    {
        if (empty($dates)) {
            return 0;
        }

        // Query mengambil MIN dan MAX scan time dari raw log yang sudah tersimpan
        $records = (new Query())
            ->select([
                'pin',
                'scan_date',
                'first_scan' => 'MIN(scan_time)',
                'last_scan'  => 'MAX(scan_time)',
                'total_taps' => 'COUNT(id)'
            ])
            ->from('{{%attendance_raw_log}}')
            ->where(['scan_date' => $dates])
            ->groupBy(['pin', 'scan_date'])
            ->all();

        $processedCount = 0;
        $minGapSeconds = $minGapMinutes * 60;

        foreach ($records as $row) {
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

            // UPSERT: Jika PIN + Date sudah ada, lakukan UPDATE (bukan INSERT ganda)
            Yii::$app->db->createCommand()->upsert('{{%attendance_daily_summary}}', [
                'pin'        => $row['pin'],
                'date'       => $row['scan_date'],
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'total_taps' => (int)$row['total_taps'],
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                // Kolom yang di-update jika record sudah ada
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'total_taps' => (int)$row['total_taps'],
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
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