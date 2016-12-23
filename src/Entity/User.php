<?php

namespace Entity;

use Leaf\DB;

/**
 * 用户
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $nickname
 * @property string $avatar
 * @property string $email
 * @property string $mobile
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class User extends \Leaf\Auth\User
{
    //状态
    const STATUS_ENABLE = 10;   // 有效
    const STATUS_DISABLE = 20;  // 无效

    public static function labels()
    {
        return [
            'id' => 'ID',
            'username' => '登录名称',
            'nickname' => '昵称',
            'avatar' => '头像',
            'email' => '邮箱',
            'mobile' => '手机',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    public function loadDefaultValues()
    {
        return DB::table(static::tableName())->loadDefaultValues($this);
    }
}