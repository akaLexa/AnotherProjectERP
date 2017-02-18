CREATE TABLE tbl_plugins_roles (
  col_prID int(11) NOT NULL AUTO_INCREMENT,
  col_pID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_prID),
  CONSTRAINT FK_tbl_plugins_role_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins (col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_role_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci;