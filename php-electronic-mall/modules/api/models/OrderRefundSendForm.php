<?php
/**
 * @copyright ©2018
 * @author Lu Wei
 * @link
 * Created by IntelliJ IDEA
 * Date Time: 2018/6/23 17:36
 */


namespace app\modules\api\models;

use app\hejiang\exceptions\InvalidResponseException;
use app\models\FormId;
use app\models\MsOrderRefund;
use app\models\OrderRefund;
use app\models\PtOrderRefund;

class OrderRefundSendForm extends ApiModel
{
    public $order_refund_id;
    public $user_id;
    public $express;
    public $express_no;
    public $orderType;
    public $form_id;

    public function rules()
    {
        return [
            [['express', 'express_no', 'orderType', 'form_id'], 'trim'],
            [['order_refund_id', 'express', 'express_no'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'express' => '快递公司',
            'express_no' => '快递单号',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->orderType === 'MIAOSHA') {
            $query = MsOrderRefund::find();

        } else if ($this->orderType === 'PINTUAN') {
            $query = PtOrderRefund::find();

        } else if ($this->orderType === 'STORE') {
            $query = OrderRefund::find();

        } else {
            return [
                'code' => 1,
                'msg' => 'orderTYpe属性未定义或值不符合预期'
            ];
        }

        $order_refund = $query->where([
            'id' => $this->order_refund_id,
            'user_id' => $this->user_id,
        ])->one();

        if (!$order_refund) {
            return [
                'code' => 1,
                'msg' => '售后订单不存在。',
            ];
        }

        $order_refund->is_user_send = 1;
        $order_refund->user_send_express = $this->express;
        $order_refund->user_send_express_no = $this->express_no;
        if ($order_refund->save()) {

            $formId = new FormId();
            $formId->store_id = $this->getCurrentStoreId();
            $formId->user_id = $this->getCurrentUserId();
            $formId->wechat_open_id = \Yii::$app->user->identity->wechat_open_id;
            $formId->form_id = $this->form_id;
            $formId->order_no = $order_refund->order_refund_no;//售后订单号
            $formId->type = 'form_id';
            $formId->addtime = time();
            $formId->save();

            return [
                'code' => 0,
                'msg' => '发货成功。',
            ];
        } else {
            return $this->getErrorResponse($order_refund);
        }
    }
}
