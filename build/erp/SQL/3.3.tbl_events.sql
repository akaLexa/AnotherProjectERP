CREATE TABLE tbl_events (
  col_evID int(11) NOT NULL AUTO_INCREMENT,
  col_dateCreate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_etID int(11) DEFAULT NULL COMMENT 'id эвента',
  col_object int(11) DEFAULT NULL COMMENT 'id объекта, на который идет ссылка',
  col_userID int(11) DEFAULT NULL,
  col_isTop char(1) DEFAULT '0' COMMENT 'закреплен в топе',
  col_isNoticed char(1) DEFAULT '0' COMMENT 'ознакомлен',
  col_isMailed char(1) DEFAULT '0' COMMENT 'отправлен по почте',
  col_comment text DEFAULT NULL,
  PRIMARY KEY (col_evID),
  CONSTRAINT FK_tbl_events_col_etID FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_events_relation (col_erID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_events_col_userID FOREIGN KEY (col_userID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 23
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'эыенты';