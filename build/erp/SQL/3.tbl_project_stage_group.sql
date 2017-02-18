CREATE TABLE tbl_project_stage_group (
  col_psgID int(11) NOT NULL AUTO_INCREMENT,
  col_gID int(11) DEFAULT NULL,
  col_StageID int(11) DEFAULT NULL,
  PRIMARY KEY (col_psgID),
  CONSTRAINT FK_tbl_project_stage_group_co2 FOREIGN KEY (col_StageID)
  REFERENCES tbl_hb_project_stage (col_StageID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_group_col FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 9
  AVG_ROW_LENGTH = 2048
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'доступ к стадиям';