CREATE
  DEFINER = CURRENT_USER
EVENT ev_taskAutoStart
  ON SCHEDULE EVERY '1' HOUR
  STARTS '2017-02-11 14:59:41'
  ON COMPLETION PRESERVE
  COMMENT 'автозапуск задач по полю col_autoStart'
DO
  BEGIN

    CALL sp_autoStartTask;

  END