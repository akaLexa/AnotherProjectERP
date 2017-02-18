CREATE TABLE tbl_menu (
  col_id int(11) NOT NULL AUTO_INCREMENT,
  col_mtitle varchar(255) DEFAULT NULL,
  col_mtype int(11) DEFAULT NULL,
  col_link varchar(255) DEFAULT NULL,
  col_modul varchar(255) DEFAULT NULL,
  col_Seq int(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 10
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;