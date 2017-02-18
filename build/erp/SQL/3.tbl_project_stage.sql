CREATE TABLE tbl_project_stage (
  col_pstageID int(11) NOT NULL AUTO_INCREMENT,
  col_projectID int(11) DEFAULT NULL,
  col_statusID int(11) DEFAULT NULL,
  col_respID int(11) DEFAULT NULL,
  col_dateCreate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_dateStart datetime DEFAULT NULL,
  col_dateStartPlan datetime DEFAULT NULL,
  col_dateEnd datetime DEFAULT NULL COMMENT 'дата окончания принятия решения',
  col_dateEndPlan datetime DEFAULT NULL,
  col_dateEndFact datetime DEFAULT NULL,
  col_comment text DEFAULT NULL,
  col_stageID int(11) DEFAULT NULL,
  col_prevStageID int(11) DEFAULT NULL COMMENT 'предыдущий статус (для возврата при отказе)',
  col_seq int(11) DEFAULT NULL COMMENT 'очередность',
  col_duration int(11) DEFAULT 1,
  PRIMARY KEY (col_pstageID),
  CONSTRAINT FK_tbl_project_stage_col_proje FOREIGN KEY (col_projectID)
  REFERENCES tbl_project (col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_respI FOREIGN KEY (col_respID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_stage FOREIGN KEY (col_stageID)
  REFERENCES tbl_hb_project_stage (col_StageID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_statu FOREIGN KEY (col_statusID)
  REFERENCES tbl_hb_status (col_StatusID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;