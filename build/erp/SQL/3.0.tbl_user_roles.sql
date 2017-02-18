CREATE TABLE tbl_user_roles (
  col_roleID int(11) NOT NULL AUTO_INCREMENT,
  col_roleName varchar(250) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
  PRIMARY KEY (col_roleID),
  UNIQUE INDEX UK_tbl_user_roles_col_roleName (col_roleName)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'роли для пользователя';