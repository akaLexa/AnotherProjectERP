CREATE TABLE tbl_hb_status (
  col_StatusID int(11) NOT NULL AUTO_INCREMENT,
  col_StatusName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_StatusID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 6
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'статусы';