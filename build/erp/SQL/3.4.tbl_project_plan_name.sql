CREATE TABLE tbl_project_plan_name (
  col_ppnID int(11) NOT NULL AUTO_INCREMENT,
  col_planName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_ppnID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'название сохраненного плана проекта';