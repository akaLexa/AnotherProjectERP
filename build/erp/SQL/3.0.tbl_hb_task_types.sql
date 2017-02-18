CREATE TABLE tbl_hb_task_types (
  col_tttID int(11) NOT NULL AUTO_INCREMENT,
  col_tName varchar(255) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
  PRIMARY KEY (col_tttID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'справочник типовых названий задач';