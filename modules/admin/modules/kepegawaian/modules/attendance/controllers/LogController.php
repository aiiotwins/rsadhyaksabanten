<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use app\modules\admin\modules\kepegawaian\modules\attendance\models\UploadLogForm;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\admin\modules\kepegawaian\modules\attendance\services\AttendanceExportService;

class LogController extends Controller
{
    public function actionUpload()
    {
        set_time_limit(0); // 0 = tanpa batas waktu eksekusi
        ini_set('memory_limit', '512M');
               
        $model = new UploadLogForm();

        if (Yii::$app->request->isPost) {
            $model->logFile = UploadedFile::getInstance($model, 'logFile');
            $model->deviceId = Yii::$app->request->post('UploadLogForm')['deviceId'] ?? null;

            if ($model->logFile) {
                $result = $model->import(Yii::$app->request->post('UploadLogForm')['deviceId']);

                if ($result['success']) {
                    Yii::$app->response->format = Response::FORMAT_JSON;

                    // Yii::$app->session->setFlash('success', $result['message']);
                    // return $this->refresh();
                    return [
                        'success' => true,
                        'message' => $result['message'],
                    ];
                }

                Yii::$app->session->setFlash('error', $result['message']);
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    public function actionExportExcel($deviceId=null, $year = null, $month = null)
    {
        $deviceId = $deviceId ?: 'A';
        $year = $year ?: (int)date('Y');
        $month = $month ?: (int)date('n');

        $service = new AttendanceExportService();
        $spreadsheet = $service->generateReport($deviceId, $year, $month);

        $fileName = sprintf('Rekap_Absensi_Uang_Makan_%04d_%02d_device_%s.xlsx', $year, $month, $deviceId);

        // Set response header untuk download Excel
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->add('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        $response->headers->add('Cache-Control', 'max-age=0');

        $writer = new Xlsx($spreadsheet);

        // Stream output langsung ke browser tanpa membuat file temporary di hard drive
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
}