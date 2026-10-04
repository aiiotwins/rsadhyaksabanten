<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\User;
use app\modules\admin\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Query;
use app\components\DataTablesHelper;
use Yii;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                
                // Generate password hash sebelum disimpan
                if (!empty($model->password_hash)) { // sesuaikan dengan nama atribut password Anda
                    $model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);
                }

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetData()
    {
        return DataTablesHelper::process([
            // 1. Query dasar
            'query' => (new Query())
                ->select(['id', 'username', 'email', 'status', 'created_at'])
                ->from('{{%user}}'),

            // 2. Mapping nomor urut kolom DataTables ke kolom database
            'columnsMap' => [
                1 => 'username',
                2 => 'email',
                3 => 'status',
                4 => 'created_at',
            ],

            // 3. Kolom yang dicari lewat input global search DataTables
            'searchableColumns' => ['username', 'email'],

            // 4. Kolom yang dicari dengan perbandingan persis / exact match (=)
            'exactMatchColumns' => [3], // index 3 adalah kolom status

            // 5. Susun format data baris
            'formatter' => function ($models, $start) {
                $data = [];
                $no = $start + 1;

                foreach ($models as $row) {
                    $statusLabel = ($row['status'] == 10)
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Nonaktif</span>';

                    $createdAt = is_numeric($row['created_at'])
                        ? date('d-m-Y H:i', $row['created_at'])
                        : ($row['created_at'] ?? '-');

                    $data[] = [
                        'no'         => $no++,
                        'username'   => Html::encode($row['username']),
                        'email'      => Html::encode($row['email']),
                        'status'     => $statusLabel,
                        'created_at' => $createdAt,
                        'action'     => Html::a('Lihat', ['view', 'id' => $row['id']], ['class' => 'btn btn-xs btn-info']),
                    ];
                }

                return $data;
            },
        ]);
    }
}
