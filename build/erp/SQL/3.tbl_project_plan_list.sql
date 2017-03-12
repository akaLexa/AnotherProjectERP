CREATE TABLE tbl_project_plan_list (
  col_pplID int(11) NOT NULL AUTO_INCREMENT,
  col_ppnID int(11) DEFAULT NULL,
  col_pstageID int(11) DEFAULT NULL,
  col_stageSeq int(11) DEFAULT NULL,
  col_stageDur int(11) DEFAULT NULL,
  col_stageID int(11) DEFAULT NULL,
  col_dateStartPlan datetime DEFAULT NULL,
  col_dateEndPlan datetime DEFAULT NULL,
  col_taskID int(11) DEFAULT NULL,
  col_taskName text DEFAULT NULL,
  col_taskStart datetime DEFAULT NULL,
  col_taskEnd datetime DEFAULT NULL,
  col_taskSeq int(11) DEFAULT NULL,
  col_taskDur int(11) DEFAULT NULL,
  col_taskNextID int(11) DEFAULT NULL,
  col_bonding char(1) DEFAULT NULL,
  col_taskDesc text DEFAULT NULL,
  PRIMARY KEY (col_pplID),
  CONSTRAINT FK_tbl_project_plan_list_col_p FOREIGN KEY (col_ppnID)
  REFERENCES tbl_project_plan_name (col_ppnID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  AVG_ROW_LENGTH = 2340
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'позиции в план';