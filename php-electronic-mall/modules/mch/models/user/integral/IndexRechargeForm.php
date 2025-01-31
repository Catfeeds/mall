<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\modules\mch\models\user\integral;

use app\models\IntegralLog;
use app\modules\mch\models\MchModel;
use app\modules\mch\models\UserExportList;
use yii\data\Pagination;

class IndexRechargeForm extends MchModel
{
    public $userId = 0;
    public $type = '';
    public $fields;
    public $flag;
    public $date_start;
    public $date_end;

    public function rules()
    {
        return [
            [['flag', 'date_start', 'date_end'], 'trim'],
            [['fields'], 'safe']
        ];
    }

    public function getIntegralRechargeList()
    {
        $query = IntegralLog::find()
            ->andWhere(['store_id' => $this->getCurrentStoreId(), 'type' => $this->type])
            ->with('user');

        if ($this->userId) {
            $query->andWhere(['user_id' => $this->userId]);
        }

        if ($this->date_start) {
            $query->andWhere(['>', 'addtime', strtotime($this->date_start)]);
        }

        if ($this->date_end) {
            $query->andWhere(['<', 'addtime', strtotime($this->date_end + 1)]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query->orderBy('addtime DESC')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all();

        if ($this->flag == "EXPORT") {
            $userExport = new UserExportList();
            $userExport->fields = $this->fields;
            $userExport->integralForm($list);
        }

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
