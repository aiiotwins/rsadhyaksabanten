<?php

namespace app\components;

use DateTime;
use yii\base\Component;

class ShiftDetector extends Component
{
    /**
     * Master acuan jadwal berdasarkan Keputusan Direktur RSA Banten
     */
    const REFERENCE_SHIFTS = [
        'MEDIS_PAGI'     => ['label' => 'Shift Pagi (Pelayanan/Medis)', 'start' => '07:30', 'end' => '15:00'],
        'MEDIS_SIANG'    => ['label' => 'Shift Siang (Pelayanan/Medis)', 'start' => '14:00', 'end' => '21:30'],
        'MEDIS_MALAM'    => ['label' => 'Shift Malam (Pelayanan/Medis)', 'start' => '20:30', 'end' => '08:00'],
        'NON_SHIFT_PAGI' => ['label' => 'Reguler / Administrasi / TU',  'start' => '07:30', 'end' => '16:00'],
        'NON_SHIFT_MED'  => ['label' => 'Reguler (Bedah/K3RS/Sanitasi)', 'start' => '08:00', 'end' => '16:00'],
        'SEC_PAGI'       => ['label' => 'Shift Pagi (Pengamanan 12 Jam)', 'start' => '08:00', 'end' => '20:00'],
        'SEC_MALAM'      => ['label' => 'Shift Malam (Pengamanan 12 Jam)','start' => '20:00', 'end' => '08:00'],
        'HK_PAGI'        => ['label' => 'Shift Pagi (HK/ME)',            'start' => '07:00', 'end' => '15:00'],
        'HK_SIANG'       => ['label' => 'Shift Siang (HK/ME)',           'start' => '14:00', 'end' => '22:00'],
        'HK_MALAM'       => ['label' => 'Shift Malam (HK/ME)',           'start' => '23:00', 'end' => '07:00'],
        'GIZI_SUBUH_1'   => ['label' => 'Shift Subuh (Dapur/HK Gizi)',   'start' => '04:00', 'end' => '12:00'],
        'GIZI_SUBUH_2'   => ['label' => 'Shift Subuh (Pramusaji/Gizi)',  'start' => '05:00', 'end' => '13:00'],
        'GIZI_SIANG'     => ['label' => 'Shift Siang (Gizi/Dapur)',      'start' => '12:00', 'end' => '20:00'],
    ];

    /**
     * Toleransi keterlambatan dalam menit (opsional, default 0 menit)
     */
    const GRACE_PERIOD_MINUTES = 0;

    /**
     * Deteksi shift sekaligus evaluasi status kedatangan dan kepulangan
     */
    public static function detect(string $checkIn, ?string $checkOut = null): array
    {
        $inMinutes = self::timeToMinutes($checkIn);
        $hasCheckOut = !empty($checkOut);
        $outMinutes = $hasCheckOut ? self::timeToMinutes($checkOut) : null;

        $bestMatch = null;
        $lowestDiff = PHP_INT_MAX;

        foreach (self::REFERENCE_SHIFTS as $code => $shift) {
            $refIn = self::timeToMinutes($shift['start']);
            $diffIn = self::getCircularMinuteDiff($inMinutes, $refIn);

            if ($hasCheckOut) {
                $refOut = self::timeToMinutes($shift['end']);
                $diffOut = self::getCircularMinuteDiff($outMinutes, $refOut);
                $totalDiff = ($diffIn * 0.6) + ($diffOut * 0.4);
            } else {
                $totalDiff = $diffIn;
            }

            if ($totalDiff < $lowestDiff) {
                $lowestDiff = $totalDiff;
                $bestMatch = array_merge($shift, ['code' => $code]);
            }
        }

        $refInMinutes = self::timeToMinutes($bestMatch['start']);
        $arrivalAnalysis = self::analyzeArrival($inMinutes, $refInMinutes);

        $departureAnalysis = null;
        if ($hasCheckOut) {
            $refOutMinutes = self::timeToMinutes($bestMatch['end']);
            $departureAnalysis = self::analyzeDeparture($outMinutes, $refOutMinutes);
        }

        return [
            'shift'            => $bestMatch,
            'confidence'       => self::calculateConfidence($lowestDiff),
            'kedatangan'       => $arrivalAnalysis,
            'kepulangan'       => $departureAnalysis,
        ];
    }

    /**
     * Hitung selisih kedatangan (lebih cepat vs terlambat)
     */
    private static function analyzeArrival(int $actualIn, int $refIn): array
    {
        // Hitung selisih relatif dengan mempertimbangkan putaran 24 jam
        $diff = $actualIn - $refIn;
        if ($diff > 720) {
            $diff -= 1440;
        } elseif ($diff < -720) {
            $diff += 1440;
        }

        if ($diff > self::GRACE_PERIOD_MINUTES) {
            return [
                'status'         => 'TERLAMBAT',
                'selisih_menit'  => $diff,
                'keterangan'     => "Terlambat {$diff} menit",
            ];
        } elseif ($diff < 0) {
            $earlyMinutes = abs($diff);
            return [
                'status'         => 'LEBIH_CEPAT',
                'selisih_menit'  => $earlyMinutes,
                'keterangan'     => "Datang lebih cepat {$earlyMinutes} menit",
            ];
        }

        return [
            'status'         => 'TEPAT_WAKTU',
            'selisih_menit'  => 0,
            'keterangan'     => 'Tepat waktu',
        ];
    }

    /**
     * Hitung selisih kepulangan (pulang cepat vs lembur/overtime)
     */
    private static function analyzeDeparture(int $actualOut, int $refOut): array
    {
        $diff = $actualOut - $refOut;
        if ($diff > 720) {
            $diff -= 1440;
        } elseif ($diff < -720) {
            $diff += 1440;
        }

        if ($diff < 0) {
            $earlyLeave = abs($diff);
            return [
                'status'         => 'PULANG_CEPAT',
                'selisih_menit'  => $earlyLeave,
                'keterangan'     => "Pulang lebih cepat {$earlyLeave} menit",
            ];
        } elseif ($diff > 0) {
            return [
                'status'         => 'LEBIH_LAMA',
                'selisih_menit'  => $diff,
                'keterangan'     => "Pulang lebih lambat {$diff} menit",
            ];
        }

        return [
            'status'         => 'TEPAT_WAKTU',
            'selisih_menit'  => 0,
            'keterangan'     => 'Tepat waktu',
        ];
    }

    private static function timeToMinutes(string $timeStr): int
    {
        $dt = new DateTime($timeStr);
        return ((int)$dt->format('H') * 60) + (int)$dt->format('i');
    }

    private static function getCircularMinuteDiff(int $timeA, int $timeB): int
    {
        $diff = abs($timeA - $timeB);
        return min($diff, 1440 - $diff);
    }

    private static function calculateConfidence(float $diff): string
    {
        if ($diff <= 30) return 'HIGH';
        if ($diff <= 75) return 'MEDIUM';
        return 'LOW';
    }
}