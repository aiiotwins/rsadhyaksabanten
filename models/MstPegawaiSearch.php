<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MstPegawai;

/**
 * MstPegawaiSearch represents the model behind the search form of `app\models\MstPegawai`.
 */
class MstPegawaiSearch extends MstPegawai
{
    /**
     * {@inheritdoc}
     */

    // 1. Deklarasikan variabel filter
    public $penempatan_departemen;
    public $nomor_sk;
    
    public function rules()
    {
        return [
            [['id', 'kode_departemen', 'fingerprint_id', 'created_at', 'updated_at'], 'integer'],
            [['kode_karyawan', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'status_kepegawaian', 'device_id'], 'safe'],
            [['penempatan_departemen', 'nomor_sk'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = MstPegawai::find()
                ->joinWith('penempatanTerbaru.departemen')
                ->orderBy(['mst_pegawai.nama' => SORT_ASC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Sorting (opsional, agar kolom bisa diklik untuk sorting urutan)
        $dataProvider->sort->attributes['penempatan_departemen'] = [
            'asc' => ['mst_departemen.nama_departemen' => SORT_ASC],
            'desc' => ['mst_departemen.nama_departemen' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['nomor_sk'] = [
            'asc' => ['riwayat_penempatan.nomor_sk' => SORT_ASC],
            'desc' => ['riwayat_penempatan.nomor_sk' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'mst_pegawai.id' => $this->id,
            'mst_pegawai.tanggal_lahir' => $this->tanggal_lahir,
            'mst_pegawai.kode_departemen' => $this->kode_departemen,
            'mst_pegawai.fingerprint_id' => $this->fingerprint_id,
            'mst_pegawai.created_at' => $this->created_at,
            'mst_pegawai.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'mst_pegawai.kode_karyawan', $this->kode_karyawan])
            ->andFilterWhere(['like', 'mst_pegawai.nama', $this->nama])
            ->andFilterWhere(['like', 'mst_pegawai.jenis_kelamin', $this->jenis_kelamin])
            ->andFilterWhere(['like', 'mst_pegawai.status_kepegawaian', $this->status_kepegawaian])
            ->andFilterWhere(['like', 'mst_pegawai.device_id', $this->device_id]);
        
        $query->andFilterWhere(['like', 'mst_departemen.nama_departemen', $this->penempatan_departemen])
            ->andFilterWhere(['like', 'riwayat_penempatan.nomor_sk', $this->nomor_sk]);

        return $dataProvider;
    }
}
