

DROP TABLE IF EXISTS pre_user;
CREATE TABLE IF NOT EXISTS pre_user(
	id INT AUTO_INCREMENT PRIMARY KEY ,
	username VARCHAR(50) NOT NULL DEFAULT '' COMMENT '登录名称',
  password_hash VARCHAR(255) NOT NULL DEFAULT '' COMMENT '密码哈希',
	nickname VARCHAR(255) NOT NULL DEFAULT '' COMMENT '昵称',
  avatar VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像',
	email VARCHAR(255) NOT NULL DEFAULT '' COMMENT '邮箱',
	mobile VARCHAR(50) NOT NULL DEFAULT '' COMMENT '手机',
	remember_token VARCHAR(255) DEFAULT '' NOT NULL COMMENT '记住',
	status INT NOT NULL DEFAULT 10 COMMENT '状态',

  created_at DATETIME NOT NULL DEFAULT '1970-01-01' COMMENT '新增时间',
  updated_at DATETIME NOT NULL DEFAULT '1970-01-01' COMMENT '修改时间',
	KEY username (username(20)),
	KEY email (email(20)),
	KEY mobile (mobile),
	KEY remember_token (remember_token(20))
)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 COMMENT '用户';


DROP TABLE IF EXISTS pre_password_reset;
CREATE TABLE `pre_password_reset` (
  `email` varchar(255)  NOT NULL,
  `token` varchar(255)  NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT '1970-01-01' COMMENT '新增时间',
	KEY email (email(20)),
	KEY token (token(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '邮箱重置密码';


DROP TABLE IF EXISTS pre_user_wechat;
CREATE TABLE IF NOT EXISTS pre_user_wechat(
	`id` INT AUTO_INCREMENT PRIMARY KEY ,
	`user_id` INT DEFAULT 0 NOT NULL,
  `appid` VARCHAR(255) NOT NULL DEFAULT '',
  `openid` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT '1970-01-01' COMMENT '新增时间',
  `updated_at` DATETIME NOT NULL DEFAULT '1970-01-01' COMMENT '修改时间',
	unique (user_id, appid, openid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 COMMENT='绑定微信';
