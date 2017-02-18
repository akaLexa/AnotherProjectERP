CREATE PROCEDURE sp_StartTaskPlan (IN stageID int)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'запуск плановых задач по стадии'
  BEGIN
    DECLARE done int DEFAULT FALSE;
    DECLARE tempTask int;

    DECLARE cu_Work CURSOR FOR
      -- первая задача + все задачи с типом старта начало - начало из 1й последовательности
      SELECT
        tt.col_taskID
      -- ,tt.col_nextID
      FROM tbl_tasks tt
      WHERE tt.col_StatusID = 5
            AND tt.col_pstageID = stageID
            AND tt.col_bonding IN (0, 2)
            AND tt.col_seq = 1
      ORDER BY tt.col_seq ASC, tt.col_bonding, tt.col_startFact, tt.col_taskDur DESC, tt.col_taskID ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cu_Work;

    read_loop:
    LOOP
      FETCH cu_Work INTO tempTask;
      IF done THEN
        LEAVE read_loop;
      END IF;

      UPDATE tbl_tasks
      SET col_StatusID = 1,
        col_startFact = NOW()
      WHERE col_taskID = tempTask;

    END LOOP;
    CLOSE cu_Work;

  END