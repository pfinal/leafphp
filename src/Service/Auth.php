<?php

namespace Service;

use Entity\User;
use Leaf\Auth\AuthManager;
use Leaf\DB;

class Auth extends AuthManager
{
    /**
     * 通过id取回用户
     *
     * @param  int $id
     * @return User
     */
    protected static function retrieveById($id)
    {
        return DB::table(User::tableName())->where(['status' => User::STATUS_ENABLE])->asEntity(User::className())->findByPk($id);
    }

    /**
     * 通过token取回用户
     *
     * @param string $token
     * @return User|null
     */
    public static function retrieveByToken($token)
    {
        return DB::table(User::tableName())->where('remember_token=?', [$token])->asEntity(User::className())->findOne();
    }

    /**
     * 更新token
     *
     * @param int $userId
     * @param string $token
     * @return bool
     */
    public static function saveRememberToken($userId, $token)
    {
        return 1 == DB::table(User::tableName())->where('id=?', [$userId])->update(['remember_token' => $token]);
    }

    /**
     * 登录前置操作，如果返回false,将允许登录
     * @param User $user
     * @param bool $fromRemember 是否来自记住我功能
     * @return bool
     */
    public static function beforeLogin($user, $fromRemember)
    {
        return $user->status == User::STATUS_ENABLE;
    }
}