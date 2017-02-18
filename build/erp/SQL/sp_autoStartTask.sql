CREATE PROCEDURE sp_autoStartTask ()
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'автозапуск задач по полю col_autoStart'
  BEGIN

    DECLARE curTask int;
    DECLARE curResp int;
    DECLARE done int DEFAULT FALSE;

    DECLARE cu_Work CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_respID
      FROM tbl_tasks tt
      WHERE tt.col_StatusID = 4
            AND tt.col_autoStart < NOW();

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cu_Work;

    read_loop:
    LOOP
      FETCH cu_Work INTO curTask, curResp;
      IF done THEN
        LEAVE read_loop;
      END IF;

      UPDATE tbl_tasks
      SET col_StatusID = 1,
        col_startFact = NOW()
      WHERE col_taskID = curTask;
      INSERT INTO tbl_tasks_comments (col_taskID, col_UserID, col_text, col_date, col_trigger)
        VALUE (curTask, 2, 'Задача запущена автоматически, так как истек срок ожидания принятия решения', NOW(), 0);

    END LOOP;
    CLOSE cu_Work;

  END