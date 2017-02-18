CREATE TABLE tbl_user_groups (
  col_gID int(11) NOT NULL AUTO_INCREMENT,
  col_gName varchar(250) DEFAULT NULL,
  PRIMARY KEY (col_gID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 6
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'группы пользователей';