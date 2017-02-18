CREATE TABLE tbl_hb_event_state (
  col_esID int(11) NOT NULL AUTO_INCREMENT,
  col_esName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_esID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 11
  AVG_ROW_LENGTH = 2048
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'статусы эвентов';