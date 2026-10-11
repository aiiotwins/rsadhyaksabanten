<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\modules\admin\modules\kepegawaian\modules\attendance\services\AttendanceService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiController extends Controller
{
    public function actionIndex()
    {
        $startDate = Yii::$app->request->get('start_date', date('Y-m-01'));
        $endDate = Yii::$app->request->get('end_date', date('Y-m-d'));
        $pin = Yii::$app->request->get('pin', null);

        $dataRekap = AttendanceService::getRekapHarian($startDate, $endDate, $pin);

        return $this->render('index', [
            'dataRekap' => $dataRekap,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'pin' => $pin,
        ]);
    }

    public function actionImport()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('file_log');
            if ($file) {
                $tempPath = Yii::getAlias('@runtime') . '/' . $file->name;
                $file->saveAs($tempPath);

                $total = AttendanceService::importLogFile($tempPath, $file->baseName);
                unlink($tempPath);

                Yii::$app->session->setFlash('success', "Berhasil mengimpor {$total} data log mesin.");
                return $this->redirect(['index']);
            }
        }
        return $this->render('import');
    }

    public function actionExportExcel($start_date, $end_date, $pin = null)
    {
        $data = AttendanceService::getRekapHarian($start_date, $end_date, $pin);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        // Header Judul
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI ABSENSI PEGAWAI');
        $sheet->setCellValue('A2', "Periode: {$start_date} s/d {$end_date}");
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(12);

        // Header Tabel
        $headers = ['No', 'PIN', 'Nama Pegawai', 'Departemen', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Terlambat (Mnt)', 'Status'];
        $colLetter = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($colLetter . '4', $h);
            $colLetter++;
        }

        // Style Header
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F497D']
            ]
        ]);

        // Isi Data
        $row = 5;
        $no = 1;
        foreach ($data as $d) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $d['pin']);
            $sheet->setCellValue('C' . $row, $d['nama']);
            $sheet->setCellValue('D' . $row, $d['departemen']);
            $sheet->setCellValue('E' . $row, $d['tanggal']);
            $sheet->setCellValue('F' . $row, $d['jam_masuk']);
            $sheet->setCellValue('G' . $row, $d['jam_pulang']);
            $sheet->setCellValue('H' . $row, $d['terlambat']);
            $sheet->setCellValue('I' . $row, $d['status']);

            // Highlight jika terlambat
            if ($d['terlambat'] > 0) {
                $sheet->getStyle('H' . $row . ':I' . $row)->getFont()->getColor()->setRGB('C00000');
            }

            $row++;
        }

        // Border & Auto width
        $sheet->getStyle('A4:I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Stream Output ke Browser
        $fileName = "Laporan_Absensi_{$start_date}_{$end_date}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}