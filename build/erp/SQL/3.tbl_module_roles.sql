CREATE TABLE tbl_module_roles (
  col_mrID int(11) NOT NULL AUTO_INCREMENT,
  col_modID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_mrID),
  CONSTRAINT FK_tbl_module_roles_tbl_modules_col_modID FOREIGN KEY (col_modID)
  REFERENCES tbl_modules (col_modID) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_roles_tbl_user_roles_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'указание ролей с правами доступа';