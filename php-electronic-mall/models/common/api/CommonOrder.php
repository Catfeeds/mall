<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\models\common\api;

use app\models\Model;
use app\models\Option;
use app\models\User;

class CommonOrder
{
    /**
     * 持续更新...
     * 下单前的检测
     */
    public static function checkOrder($other = [])
    {
        $user = \Yii::$app->user->identity;

        if ($user->blacklist) {
            return [
                'code' => 1,
                'msg' => '无法下单'
            ];
        }

        if (isset($other['mobile']) && $other['mobile']) {
            $option = Option::getList('mobile_verify', \Yii::$app->controller->store->id, 'admin', 1);
            if ($option['mobile_verify']) {
                if (!preg_match(Model::MOBILE_VERIFY, $other['mobile'])) {
                    return [
                        'code' => 1,
                        'msg' => '请输入正确的手机号'
                    ];
                }
            }
        }
    }

    /**
     * 分销 保存上级的ID(用于先成为上下级，再成为分销商)
     * @param $parentId
     * @return static
     */
    public static function saveParentId($parentId)
    {
        if (!$parentId) {
            return;
        }

        // 父级用户不存在
        $parentUser = User::findOne($parentId);
        if (!$parentUser) {
            return;
        }

        $user = \Yii::$app->user->identity;

        if ($user) {
            $user->parent_user_id = $parentId;
            $user->save();
        }

        return $user;
    }
}