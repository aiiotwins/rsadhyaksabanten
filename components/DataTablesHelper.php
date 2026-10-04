<?php

namespace app\components;

use Yii;
use yii\web\Response;
use yii\db\Query;

class DataTablesHelper
{
    /**
     * Memproses request DataTables dan mengembalikan format JSON
     *
     * @param array $config Konfigurasi query, mapping kolom, search, dan formatter
     * @return array
     */
    public static function process(array $config)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request           = Yii::$app->request;
        $draw              = (int) $request->post('draw', 1);
        $start             = (int) $request->post('start', 0);
        $length            = (int) $request->post('length', 10);
        $search            = $request->post('search');
        $order             = $request->post('order');
        $columns           = $request->post('columns', []);

        /** @var Query $query */
        $query             = $config['query'] ?? null;
        $columnsMap        = $config['columnsMap'] ?? [];
        $searchableColumns = $config['searchableColumns'] ?? [];
        $exactMatchColumns = $config['exactMatchColumns'] ?? [];
        $formatter         = $config['formatter'] ?? null;

        if (!$query instanceof Query) {
            throw new \yii\base\InvalidConfigException('Konfigurasi "query" harus merupakan instance dari yii\db\Query.');
        }

        // 1. Total records tanpa filter
        $totalRecords = (clone $query)->count('*');

        // 2. Global Search
        if (!empty($search['value']) && !empty($searchableColumns)) {
            $searchValue = trim($search['value']);
            $conditions = ['or'];
            foreach ($searchableColumns as $col) {
                $conditions[] = ['like', $col, $searchValue];
            }
            $query->andFilterWhere($conditions);
        }

        // 3. Filter per Kolom
        if (!empty($columns) && is_array($columns)) {
            foreach ($columns as $idx => $colData) {
                $filterVal = trim($colData['search']['value'] ?? '');
                if ($filterVal !== '' && isset($columnsMap[$idx])) {
                    $dbCol = $columnsMap[$idx];

                    if (in_array($idx, $exactMatchColumns, true) || in_array($dbCol, $exactMatchColumns, true)) {
                        $query->andFilterWhere([$dbCol => $filterVal]);
                    } else {
                        $query->andFilterWhere(['like', $dbCol, $filterVal]);
                    }
                }
            }
        }

        // 4. Total records setelah difilter
        $filteredRecords = (clone $query)->count('*');

        // 5. Dynamic Sorting
        if (!empty($order) && isset($order[0]['column'])) {
            $orderColIdx = (int) $order[0]['column'];
            $orderDir    = ($order[0]['dir'] === 'asc') ? SORT_ASC : SORT_DESC;

            if (isset($columnsMap[$orderColIdx])) {
                $query->orderBy([$columnsMap[$orderColIdx] => $orderDir]);
            }
        }

        // 6. Paginasi
        $models = $query->offset($start)
                        ->limit($length)
                        ->all();

        // 7. Format baris data
        $data = [];
        if (is_callable($formatter)) {
            $data = call_user_func($formatter, $models, $start);
        } else {
            $no = $start + 1;
            foreach ($models as $row) {
                $row['no'] = $no++;
                $data[] = $row;
            }
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => (int) $totalRecords,
            'recordsFiltered' => (int) $filteredRecords,
            'data'            => $data,
        ];
    }
}