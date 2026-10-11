<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\services;

use Yii;
use yii\base\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceService extends Component
{
    /**
     * Parsing isi file txt/dat dan import ke tabel absensi_log
     */
    public static function importLogFile($filePath, $sourceMachine = 'MESIN')
    {
        $handle = fopen($filePath, "r");
        if (!$handle) {
            return 0;
        }

        $inserted = 0;
        $batchData = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            // Memisahkan data berdasarkan TAB atau Spasi beruntun
            $cols = preg_split('/\t+|\s{2,}/', $line);

            if (count($cols) >= 2) {
                $pin = trim($cols[0]);
                $waktu = trim($cols[1]);
                $statusMesin = isset($cols[3]) ? intval($cols[3]) : 0;

                $batchData[] = [$pin, $waktu, $statusMesin, $sourceMachine];
                $inserted++;

                // Batch insert tiap 1.000 record
                if (count($batchData) >= 1000) {
                    Yii::$app->db->createCommand()
                        ->batchInsert('absensi_log', ['pin', 'waktu_absen', 'status_mesin', 'mesin_sumber'], $batchData)
                        ->execute();
                    $batchData = [];
                }
            }
        }
        fclose($handle);

        if (!empty($batchData)) {
            Yii::$app->db->createCommand()
                ->batchInsert('absensi_log', ['pin', 'waktu_absen', 'status_mesin', 'mesin_sumber'], $batchData)
                ->execute();
        }

        return $inserted;
    }

    /**
     * Rekapitulasi absensi harian per PIN
     */
    public static function getRekapHarian($startDate, $endDate, $pin = null)
    {
        $query = (new \yii\db\Query())
            ->select([
                'a.pin',
                'COALESCE(k.nama, CONCAT("Pegawai ", a.pin)) AS nama',
                'COALESCE(k.kode_departemen, "-") AS departemen',
                'DATE(a.waktu_absen) AS tanggal',
                'MIN(a.waktu_absen) AS jam_masuk',
                'MAX(a.waktu_absen) AS jam_pulang',
                'COUNT(a.id) AS total_tap'
            ])
            ->from(['a' => 'absensi_log'])
            ->leftJoin(['k' => 'mst_pegawai'], 'a.pin = k.fingerprint_id')
            ->where(['between', 'DATE(a.waktu_absen)', $startDate, $endDate])
            ->groupBy(['a.pin', 'DATE(a.waktu_absen)'])
            ->orderBy(['tanggal' => SORT_ASC, 'a.pin' => SORT_ASC]);

        if ($pin) {
            $query->andWhere(['a.pin' => $pin]);
        }

        $records = $query->all();
        $results = [];

        // Jam acuan kerja standar
        $jamMasukNormal = '08:00:00';
        $jamPulangNormal = '16:00:00';

        foreach ($records as $r) {
            $masuk = date('H:i:s', strtotime($r['jam_masuk']));
            $pulang = ($r['jam_masuk'] !== $r['jam_pulang']) ? date('H:i:s', strtotime($r['jam_pulang'])) : '-';

            // Hitung keterlambatan (toleransi 08:00:00)
            $terlambatMenit = 0;
            if ($masuk > $jamMasukNormal) {
                $terlambatMenit = round((strtotime($masuk) - strtotime($jamMasukNormal)) / 60);
            }

            // Hitung pulang cepat
            $pulangCepatMenit = 0;
            if ($pulang !== '-' && $pulang < $jamPulangNormal) {
                $pulangCepatMenit = round((strtotime($jamPulangNormal) - strtotime($pulang)) / 60);
            }

            $results[] = [
                'pin' => $r['pin'],
                'nama' => $r['nama'],
                'departemen' => $r['departemen'],
                'tanggal' => $r['tanggal'],
                'jam_masuk' => $masuk,
                'jam_pulang' => $pulang,
                'terlambat' => $terlambatMenit,
                'pulang_cepat' => $pulangCepatMenit,
                'status' => ($terlambatMenit > 0) ? 'Terlambat' : 'Tepat Waktu',
            ];
        }

        return $results;
    }
}