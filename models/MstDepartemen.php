<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mst_departemen".
 *
 * @property string $kode_departemen
 * @property string $nama_departemen
 *
 * @property RiwayatPenempatan[] $riwayatPenempatans
 */
class MstDepartemen extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mst_departemen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kode_departemen', 'nama_departemen'], 'required'],
            [['kode_departemen'], 'string', 'max' => 20],
            [['nama_departemen'], 'string', 'max' => 150],
            [['kode_departemen'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'kode_departemen' => 'Kode Departemen',
            'nama_departemen' => 'Nama Departemen',
        ];
    }

    /**
     * Gets query for [[RiwayatPenempatans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRiwayatPenempatans()
    {
        return $this->hasMany(RiwayatPenempatan::class, ['kode_departemen' => 'kode_departemen']);
    }

}
