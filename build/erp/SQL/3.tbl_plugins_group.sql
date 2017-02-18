CREATE TABLE tbl_plugins_group (
  col_pgID int(11) NOT NULL AUTO_INCREMENT,
  col_pID int(11) DEFAULT NULL,
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_pgID),
  CONSTRAINT FK_tbl_plugins_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_group_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins (col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 18
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'группы и плагины';