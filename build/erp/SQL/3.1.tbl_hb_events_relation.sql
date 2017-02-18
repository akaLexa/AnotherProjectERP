CREATE TABLE tbl_hb_events_relation (
  col_erID int(11) NOT NULL AUTO_INCREMENT,
  col_etID int(11) DEFAULT NULL,
  col_esID int(11) DEFAULT NULL,
  col_message text DEFAULT NULL,
  PRIMARY KEY (col_erID),
  CONSTRAINT FK_tbl_hb_events_relation_col_ FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_event_type (col_etID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_hb_events_relation_col2 FOREIGN KEY (col_esID)
  REFERENCES tbl_hb_event_state (col_esID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 20
  AVG_ROW_LENGTH = 862
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'готовые эвенты';