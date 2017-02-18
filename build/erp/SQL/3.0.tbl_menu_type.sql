CREATE TABLE tbl_menu_type (
  col_id int(11) NOT NULL AUTO_INCREMENT,
  col_ttitle varchar(255) DEFAULT NULL,
  col_seq int(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;