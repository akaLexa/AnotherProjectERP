CREATE TABLE tbl_roles_in_group (
  col_rigID int(11) NOT NULL AUTO_INCREMENT,
  col_gID int(11) DEFAULT NULL COMMENT 'группа',
  col_roleID int(11) DEFAULT NULL COMMENT 'роль',
  PRIMARY KEY (col_rigID),
  CONSTRAINT FK_tbl_roles_in_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_roles_in_group_col_role FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'к какой группе, какая роль принадлежит';