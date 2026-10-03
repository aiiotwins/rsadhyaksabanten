<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\modules\admin\modules\kepegawaian\modules\attendance\services\AttendanceService;

class UploadLogForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $logFile;
    public $deviceId;

    public function rules()
    {
        return [
            [['logFile'], 'required'],
            [['deviceId'], 'integer'],
            [['logFile'], 'file',
                'skipOnEmpty' => false,
                'extensions' => ['dat', 'txt'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 15 * 1024 * 1024
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'logFile' => 'File Log Mesin (.dat / .txt)',
            'deviceId' => 'Device ID / No. Mesin',
        ];
    }

    public function import()
    {
        if (!$this->validate()) {
            return ['success' => false, 'message' => 'Validasi file gagal.'];
        }

        $handle = fopen($this->logFile->tempName, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Gagal membaca file log.'];
        }

        $batchRows = [];
        $chunkSize = 1000;
        $totalRawInserted = 0;
        $totalRawRead = 0;

        // Throttling in-memory (60 detik)
        $throttleSeconds = 60;
        $lastScanTimePerPin = [];
        $affectedDates = [];

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $cols = preg_split('/\s+/', $line);
                if (count($cols) >= 7) {
                    $pin = trim($cols[0]);
                    $scanTime = $cols[1] . ' ' . $cols[2];
                    $status = (int)$cols[3];
                    $inOutMode = (int)$cols[4];
                    $verifyMode = (int)$cols[5];
                    $workCode = isset($cols[6]) ? (int)$cols[6] : 0;
                } elseif (count($cols) === 6) {
                    $pin = trim($cols[0]);
                    $scanTime = $cols[1];
                    $status = (int)$cols[2];
                    $inOutMode = (int)$cols[3];
                    $verifyMode = (int)$cols[4];
                    $workCode = (int)$cols[5];
                } else {
                    continue;
                }

                $timeUnix = strtotime($scanTime);
                if (!$timeUnix) {
                    continue;
                }

                $totalRawRead++;

                // 1. Double-Tap Throttling (In-Memory)
                if (isset($lastScanTimePerPin[$pin])) {
                    if (abs($timeUnix - $lastScanTimePerPin[$pin]) < $throttleSeconds) {
                        continue;
                    }
                }
                $lastScanTimePerPin[$pin] = $timeUnix;

                $scanDate = date('Y-m-d', $timeUnix);
                $affectedDates[$scanDate] = true;

                $batchRows[] = [
                    $this->deviceId ?: null,
                    $pin,
                    $scanTime,
                    $scanDate,
                    $status,
                    $inOutMode,
                    $verifyMode,
                    $workCode,
                    0,
                    date('Y-m-d H:i:s'),
                ];

                if (count($batchRows) >= $chunkSize) {
                    $inserted = $this->executeBatch($batchRows);
                    $totalRawInserted += $inserted;
                    $batchRows = [];
                }
            }

            if (!empty($batchRows)) {
                $inserted = $this->executeBatch($batchRows);
                $totalRawInserted += $inserted;
            }

            fclose($handle);
            $transaction->commit();

            // 2. Agregasi Rekap Harian (Hanya dijalankan jika ada tanggal terkait)
            $summaryCount = 0;
            if (!empty($affectedDates)) {
                $service = new AttendanceService();
                $summaryCount = $service->processDates(array_keys($affectedDates));
            }

            $skipped = $totalRawRead - $totalRawInserted;

            return [
                'success' => true,
                'totalRead' => $totalRawRead,
                'totalInserted' => $totalRawInserted,
                'totalSkipped' => $skipped,
                'totalSummary' => $summaryCount,
                'message' => "Proses selesai. Dibaca: {$totalRawRead} baris. Data baru masuk: {$totalRawInserted}. Duplikat diabaikan: {$skipped}. Rekap harian diperbarui: {$summaryCount}. Waktu : ".date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            fclose($handle);
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Menyimpan batch baris dengan mengabaikan data duplikat yang melanggar UNIQUE key.
     * Mengembalikan jumlah record yang benar-benar tersimpan.
     */
    private function executeBatch(array $rows)
    {
        $columns = [
            'device_id', 'pin', 'scan_time', 'scan_date',
            'status', 'in_out_mode', 'verify_mode', 'work_code',
            'is_processed', 'created_at'
        ];

        $sql = Yii::$app->db->queryBuilder->batchInsert(AttendanceRawLog::tableName(), $columns, $rows);

        // Ubah query standar menjadi INSERT IGNORE (MySQL/MariaDB)
        if (Yii::$app->db->driverName === 'mysql') {
            $sql = preg_replace('/^INSERT /i', 'INSERT IGNORE ', $sql, 1);
        }

        // execute() mengembalikan jumlah baris yang terdampak (affected rows).
        // Pada INSERT IGNORE, baris yang duplikat bernilai 0.
        return (int)Yii::$app->db->createCommand($sql)->execute();
    }
}