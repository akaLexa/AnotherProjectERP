CREATE TABLE tbl_modules (
  col_modID int(11) NOT NULL AUTO_INCREMENT,
  col_title varchar(255) DEFAULT NULL COMMENT 'идентификатор названия страницы',
  col_path varchar(255) DEFAULT NULL COMMENT 'местонахождения скрипта',
  col_cache int(11) DEFAULT 0 COMMENT 'кеширование в секундах',
  col_isClass char(1) DEFAULT '1' COMMENT 'mvc или обычный скрипт',
  col_moduleName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_modID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 11
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'модули системы';