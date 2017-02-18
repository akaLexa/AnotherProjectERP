CREATE TABLE tbl_hb_event_type (
  col_etID int(11) NOT NULL AUTO_INCREMENT,
  col_etName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_etID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'типы событий';