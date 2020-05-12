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
     * @param int $id
     * @return User
     */
    protected static function retrieveById($id)
    {
        return User::where(['status' => User::STATUS_ENABLE])->findByPk($id);
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
