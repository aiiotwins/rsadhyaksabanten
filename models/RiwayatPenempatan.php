<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "riwayat_penempatan".
 *
 * @property string $id_riwayat
 * @property string $kode_karyawan
 * @property string $kode_departemen
 * @property string|null $nomor_sk
 * @property string $tanggal_mulai
 * @property string|null $tanggal_selesai
 * @property string|null $is_aktif
 * @property string|null $created_at
 *
 * @property MstDepartemen $kodeDepartemen
 * @property MstPegawai $kodeKaryawan
 */
class RiwayatPenempatan extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const IS_AKTIF_0 = '0';
    const IS_AKTIF_1 = '1';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'riwayat_penempatan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nomor_sk', 'tanggal_selesai'], 'default', 'value' => null],
            [['is_aktif'], 'default', 'value' => 1],
            [['id_riwayat', 'kode_karyawan', 'kode_departemen', 'tanggal_mulai'], 'required'],
            [['tanggal_mulai', 'tanggal_selesai', 'created_at'], 'safe'],
            [['is_aktif'], 'string'],
            [['id_riwayat'], 'string', 'max' => 255],
            [['kode_karyawan'], 'string', 'max' => 30],
            [['kode_departemen'], 'string', 'max' => 20],
            [['nomor_sk'], 'string', 'max' => 100],
            ['is_aktif', 'in', 'range' => array_keys(self::optsIsAktif())],
            [['id_riwayat'], 'unique'],
            [['kode_departemen'], 'exist', 'skipOnError' => true, 'targetClass' => MstDepartemen::class, 'targetAttribute' => ['kode_departemen' => 'kode_departemen']],
            [['kode_karyawan'], 'exist', 'skipOnError' => true, 'targetClass' => MstPegawai::class, 'targetAttribute' => ['kode_karyawan' => 'kode_karyawan']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_riwayat' => 'Id Riwayat',
            'kode_karyawan' => 'Kode Karyawan',
            'kode_departemen' => 'Kode Departemen',
            'nomor_sk' => 'Nomor Sk',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_selesai' => 'Tanggal Selesai',
            'is_aktif' => 'Is Aktif',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[KodeDepartemen]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartemen()
    {
        // Sesuaikan nama class model dan primary key tabel departemen Anda
        return $this->hasOne(MstDepartemen::class, ['kode_departemen' => 'kode_departemen']);
    }

    /**
     * Gets query for [[KodeKaryawan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getKodeKaryawan()
    {
        return $this->hasOne(MstPegawai::class, ['kode_karyawan' => 'kode_karyawan']);
    }


    /**
     * column is_aktif ENUM value labels
     * @return string[]
     */
    public static function optsIsAktif()
    {
        return [
            self::IS_AKTIF_0 => '0',
            self::IS_AKTIF_1 => '1',
        ];
    }

    /**
     * @return string
     */
    public function displayIsAktif()
    {
        return self::optsIsAktif()[$this->is_aktif];
    }

    /**
     * @return bool
     */
    public function isIsAktif0()
    {
        return $this->is_aktif === self::IS_AKTIF_0;
    }

    public function setIsAktifTo0()
    {
        $this->is_aktif = self::IS_AKTIF_0;
    }

    /**
     * @return bool
     */
    public function isIsAktif1()
    {
        return $this->is_aktif === self::IS_AKTIF_1;
    }

    public function setIsAktifTo1()
    {
        $this->is_aktif = self::IS_AKTIF_1;
    }
}
