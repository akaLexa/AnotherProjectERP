CREATE TABLE tbl_hb_doc_group (
  col_dgID int(11) NOT NULL AUTO_INCREMENT,
  col_docGroupName varchar(255) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
  PRIMARY KEY (col_dgID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'справочник групп документов';