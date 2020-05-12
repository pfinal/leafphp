<?php


use Phinx\Migration\AbstractMigration;

class CreateUserTable extends AbstractMigration
{
    //php console migrate
    public function up()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `user`(
  id INT AUTO_INCREMENT PRIMARY KEY ,
  username VARCHAR(50) NOT NULL DEFAULT '' COMMENT '帐号',
  password_hash VARCHAR(255) NOT NULL DEFAULT '',
  nickname VARCHAR(255) NOT NULL DEFAULT '' COMMENT '昵称',
  avatar VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像',
  email VARCHAR(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  mobile VARCHAR(50) NOT NULL DEFAULT '' COMMENT '手机',
  status INT NOT NULL DEFAULT 1 COMMENT '状态',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  updated_at DATETIME COMMENT '修改时间',
  KEY username (username(20)),
  KEY email (email(20)),
  KEY mobile (mobile(11))
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '用户';
SQL;

        $sql = \Leaf\DB::getConnection()->quoteSql($sql);
        $parser = new \PhpMyAdmin\SqlParser\Parser($sql);

        foreach ($parser->errors as $error) {
            throw $error;
        }

        foreach ($parser->statements as $statement) {
            $this->execute($statement->build());
        }
    }

    public function down()
    {
    }
}
