CREATE TABLE tbl_project_stage_role (
  col_psrID int(11) NOT NULL AUTO_INCREMENT,
  col_psgID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_psrID),
  CONSTRAINT FK_tbl_project_stage_role_col_ FOREIGN KEY (col_psgID)
  REFERENCES tbl_project_stage_group (col_psgID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_role_col2 FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'доступ через группы ролей к стадиям';