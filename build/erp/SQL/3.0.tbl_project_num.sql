CREATE TABLE tbl_project_num (
  col_pnID int(11) NOT NULL AUTO_INCREMENT,
  col_serNum int(11) DEFAULT 1,
  PRIMARY KEY (col_pnID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'серийные номера';