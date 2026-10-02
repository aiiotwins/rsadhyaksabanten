<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\services;

use Yii;
use yii\db\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceExportService
{
    /**
     * Generate file Excel laporan rekap absensi uang makan
     *
     * @param int $year  Tahun (misal: 2026)
     * @param int $month Bulan (misal: 9)
     * @return Spreadsheet
     */
    public function generateReport($year, $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $totalDays = (int)date('t', strtotime($startDate));
        $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $totalDays);

        // 1. Tentukan hari kerja (Senin-Jumat) dan hari libur (Sabtu-Minggu) dalam bulan terkait
        $workdays = [];
        $holidays = [];
        for ($d = 1; $d <= $totalDays; $d++) {
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dayOfWeek = (int)date('N', strtotime($currentDate)); // 1 (Senin) s/d 7 (Minggu)
            if ($dayOfWeek >= 6) {
                $holidays[] = $d; // Sabtu / Minggu
            } else {
                $workdays[] = $d; // Senin - Jumat
            }
        }
        $totalJHK = count($workdays); // Standar Hari Kerja (misal 22 hari)

        // 2. Ambil data dari tabel attendance_daily_summary
        $logs = (new Query())
            ->select([
                'pin',
                'date',
                'day' => 'DAY(date)',
                'clock_in',
                'clock_out',
                'duration_hours' => 'ROUND(TIMESTAMPDIFF(SECOND, clock_in, clock_out) / 3600, 1)',
                'status'
            ])
            ->from('{{%attendance_daily_summary}}')
            ->where(['between', 'date', $startDate, $endDate])
            ->andWhere(['not', ['clock_in' => null]])
            ->orderBy(['pin' => SORT_ASC, 'date' => SORT_ASC])
            ->all();

        // 3. Kelompokkan per PIN (Nama Pegawai)
        $groupedData = [];
        foreach ($logs as $row) {
            $pin = $row['pin'];
            if (!isset($groupedData[$pin])) {
                $groupedData[$pin] = [
                    'pin' => $pin,
                    'total_hours' => 0.0,
                    'attended_days' => [],
                    'sakit' => 0,
                    'cuti' => 0,
                    'dl' => 0,
                    'ijin' => 0,
                    'tak' => 0,
                    'keterangan' => [],
                ];
            }

            $dayNum = (int)$row['day'];
            $groupedData[$pin]['attended_days'][] = $dayNum;
            if ($row['duration_hours'] !== null) {
                $groupedData[$pin]['total_hours'] += (float)$row['duration_hours'];
            }

            // Hitung status khusus jika ada
            $st = strtoupper((string)$row['status']);
            if ($st === 'SAKIT') {
                $groupedData[$pin]['sakit']++;
                $groupedData[$pin]['keterangan'][] = "Sakit tgl {$dayNum}";
            } elseif ($st === 'CUTI') {
                $groupedData[$pin]['cuti']++;
            } elseif ($st === 'DL') {
                $groupedData[$pin]['dl']++;
            } elseif ($st === 'IJIN') {
                $groupedData[$pin]['ijin']++;
            }
        }

        // 4. Inisialisasi PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Uang Makan');
        $sheet->setShowGridLines(true);

        // Atur font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // --- HEADER LAPORAN ---
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[(int)$month] ?? date('F');

        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'RUMAH SAKIT ADHYAKSA BANTEN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', sprintf('ABSENSI Periode 01 %s s/d %02d %s %04d (Untuk Pengajuan Uang Makan)', $monthName, $totalDays, $monthName, $year));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- HEADER TABEL (Baris 4) ---
        $headers = [
            'A4' => 'NO',
            'B4' => 'NAMA',
            'C4' => 'NIP',
            'D4' => 'JABATAN',
            'E4' => 'SAKIT',
            'F4' => 'CUTI',
            'G4' => 'DL',
            'H4' => 'IJIN',
            'I4' => 'TAK',
            'J4' => 'Jam',
            'K4' => 'JHK',
            'L4' => 'JMK',
            'M4' => 'LIBUR PADA HARI KERJA',
            'N4' => 'MASUK PADA HARI LIBUR',
            'O4' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];
        $sheet->getStyle('A4:O4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(32);

        // --- PENGISIAN BARIS DATA ---
        $rowIdx = 5;
        $no = 1;

        foreach ($groupedData as $pin => $data) {
            $attended = array_unique($data['attended_days']);
            $jmk = count($attended); // Jumlah Masuk Kerja

            // Libur pada hari kerja = hari kerja yang tidak ada di daftar kehadiran
            $absentWorkdays = array_diff($workdays, $attended);
            $liburPadaHariKerjaStr = !empty($absentWorkdays) ? implode(',', $absentWorkdays) : '0';

            // Masuk pada hari libur = kehadiran yang bertepatan dengan Sabtu/Minggu
            $attendedHolidays = array_intersect($holidays, $attended);
            $masukPadaHariLiburStr = !empty($attendedHolidays) ? implode(',', $attendedHolidays) : '0';

            $keteranganStr = !empty($data['keterangan']) ? implode(', ', $data['keterangan']) : '0';

            $sheet->setCellValue("A{$rowIdx}", $no);
            $sheet->setCellValue("B{$rowIdx}", $data['pin']); // PIN sebagai NAMA
            $sheet->setCellValue("C{$rowIdx}", ''); // NIP (opsional)
            $sheet->setCellValue("D{$rowIdx}", ''); // JABATAN (opsional)
            $sheet->setCellValue("E{$rowIdx}", $data['sakit']);
            $sheet->setCellValue("F{$rowIdx}", $data['cuti']);
            $sheet->setCellValue("G{$rowIdx}", $data['dl']);
            $sheet->setCellValue("H{$rowIdx}", $data['ijin']);
            $sheet->setCellValue("I{$rowIdx}", $data['tak']);
            $sheet->setCellValue("J{$rowIdx}", $data['total_hours']);
            $sheet->setCellValue("K{$rowIdx}", $totalJHK);
            $sheet->setCellValue("L{$rowIdx}", $jmk);
            $sheet->setCellValue("M{$rowIdx}", $liburPadaHariKerjaStr);
            $sheet->setCellValue("N{$rowIdx}", $masukPadaHariLiburStr);
            $sheet->setCellValue("O{$rowIdx}", $keteranganStr);

            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $no++;
            $rowIdx++;
        }

        $lastDataRow = $rowIdx - 1;

        // Styling Border & Alignment Baris Data
        if ($lastDataRow >= 5) {
            $sheet->getStyle("A5:O{$lastDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);

            // Format Center untuk kolom numerik & status
            $sheet->getStyle("A5:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B5:B{$lastDataRow}")->getFont()->setBold(true);
            $sheet->getStyle("E5:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M5:N{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // --- FOOTER TANDA TANGAN (Sesuai Template) ---
        $signStartRow = $rowIdx + 2;
        $sheet->setCellValue("I{$signStartRow}", sprintf('Serang,         %s %04d', $monthName, $year));
        
        $r1 = $signStartRow + 1;
        $sheet->setCellValue("I{$r1}", 'Menyetujui,');
        
        $r2 = $signStartRow + 2;
        $sheet->setCellValue("I{$r2}", 'Kepala Subbagian Umum');
        
        $r3 = $signStartRow + 3;
        $sheet->setCellValue("I{$r3}", 'Rumah Sakit Adhyaksa Banten');

        $rName = $signStartRow + 8;
        $sheet->setCellValue("I{$rName}", 'Imanuel Ade Salmon, S.H.');
        $sheet->getStyle("I{$rName}")->getFont()->setBold(true);

        $rNip = $signStartRow + 9;
        $sheet->setCellValue("I{$rNip}", 'Madya Wira NIP. 19841217 200312 1 002');

        // Atur Lebar Kolom Presisi
        $colWidths = [
            'A' => 6,
            'B' => 32,
            'C' => 18,
            'D' => 18,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 8,
            'J' => 10,
            'K' => 8,
            'L' => 8,
            'M' => 25,
            'N' => 25,
            'O' => 25,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $spreadsheet;
    }
}