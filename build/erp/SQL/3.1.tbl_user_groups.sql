CREATE TABLE tbl_user_groups (
  col_gID int(11) NOT NULL AUTO_INCREMENT,
  col_gName varchar(250) DEFAULT NULL,
  col_founder int(11) DEFAULT NULL,
  PRIMARY KEY (col_gID),
  CONSTRAINT FK_tbl_user_groups_col_founder FOREIGN KEY (col_founder)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 6
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'группы пользователей';