CREATE TABLE tbl_project (
  col_projectID int(11) NOT NULL AUTO_INCREMENT,
  col_projectName varchar(200) DEFAULT NULL,
  col_pnID int(11) DEFAULT NULL,
  col_repeat char(3) DEFAULT '1' COMMENT 'повторения',
  col_founderID int(11) DEFAULT NULL,
  col_CreateDate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_Desc text DEFAULT NULL,
  col_ProjectPlanState char(1) DEFAULT '0' COMMENT '0/1 не запущен, запущен план проекта',
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_projectID),
  CONSTRAINT FK_tbl_project_col_founderID FOREIGN KEY (col_founderID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_col_pnID FOREIGN KEY (col_pnID)
  REFERENCES tbl_project_num (col_pnID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci;