<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mst_pegawai".
 *
 * @property int $id
 * @property string|null $kode_karyawan NIP / NIK / Kode Karyawan
 * @property string $nama
 * @property string|null $jenis_kelamin
 * @property string|null $tanggal_lahir
 * @property string|null $status_kepegawaian PNS, CPNS, PPPK
 * @property int|null $kode_departemen
 * @property int|null $fingerprint_id
 * @property string|null $device_id
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class MstPegawai extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mst_pegawai';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kode_karyawan', 'jenis_kelamin', 'tanggal_lahir', 'status_kepegawaian', 'kode_departemen', 'fingerprint_id', 'device_id', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['nama'], 'required'],
            [['tanggal_lahir'], 'safe'],
            [['kode_departemen', 'fingerprint_id', 'created_at', 'updated_at'], 'integer'],
            [['kode_karyawan', 'status_kepegawaian'], 'string', 'max' => 30],
            [['nama'], 'string', 'max' => 150],
            [['jenis_kelamin'], 'string', 'max' => 10],
            [['device_id'], 'string', 'max' => 2],
            [['kode_karyawan'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kode_karyawan' => 'Kode Karyawan',
            'nama' => 'Nama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tanggal_lahir' => 'Tanggal Lahir',
            'status_kepegawaian' => 'Status Kepegawaian',
            'kode_departemen' => 'Kode Departemen',
            'fingerprint_id' => 'Fingerprint ID',
            'device_id' => 'Device ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getPenempatanTerbaru()
    {
        return $this->hasOne(RiwayatPenempatan::class, ['kode_karyawan' => 'kode_karyawan'])
            ->onCondition(['riwayat_penempatan.is_aktif' => '1']); 
    }

    // Opsi B: Mengambil tanggal mulai terbaru (fallback jika is_aktif tidak konsisten)
    public function getPenempatanTerakhir()
    {
        return $this->hasOne(RiwayatPenempatan::class, ['kode_karyawan' => 'kode_karyawan'])
            ->orderBy(['riwayat_penempatan.tanggal_mulai' => SORT_DESC, 'riwayat_penempatan.created_at' => SORT_DESC]);
    }

}
