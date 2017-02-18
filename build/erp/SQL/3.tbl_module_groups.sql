CREATE TABLE tbl_module_groups (
  col_mgID int(11) NOT NULL AUTO_INCREMENT,
  col_modID int(11) DEFAULT NULL,
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_mgID),
  CONSTRAINT FK_tbl_module_groups_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_groups_col_modID FOREIGN KEY (col_modID)
  REFERENCES tbl_modules (col_modID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 16
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'разрешения групп к модулям';