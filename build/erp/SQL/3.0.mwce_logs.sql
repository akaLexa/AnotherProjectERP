CREATE TABLE mwce_logs (
  col_mlID int(11) NOT NULL AUTO_INCREMENT,
  col_ErrNum char(3) DEFAULT '0' COMMENT 'максимум 999 номеров ошибок',
  col_msg text DEFAULT NULL,
  col_mname varchar(255) DEFAULT NULL,
  col_createTime datetime DEFAULT NULL,
  tbuild varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_mlID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci;