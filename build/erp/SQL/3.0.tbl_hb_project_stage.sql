CREATE TABLE tbl_hb_project_stage (
  col_StageID int(11) NOT NULL AUTO_INCREMENT,
  col_StageName varchar(200) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
  PRIMARY KEY (col_StageID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'стадии проекта';