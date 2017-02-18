CREATE PROCEDURE sp_setTaskPlanQuenue (IN stageID int, IN dateStart datetime, IN relationID int)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'Рассчет планового запуска задач по стадии'
  BEGIN
    DECLARE curSeq int; -- очередность выпоплнения
    DECLARE curID int; -- текущий полученный перент
    DECLARE curType char(1); -- тип связи
    DECLARE curTask int;
    DECLARE taskDur int;
    DECLARE planStart datetime;
    DECLARE planEnd datetime;
    DECLARE done int DEFAULT FALSE;

    DECLARE taskCursor CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_nextID,
        tt.col_bonding,
        tt.col_taskDur
      FROM tbl_tasks tt
      WHERE tt.col_pstageID = stageID
      ORDER BY tt.col_seq, tt.col_taskID;

    DECLARE taskRelationCur CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_nextID,
        tt.col_bonding,
        tt.col_taskDur
      FROM tbl_tasks tt
      WHERE tt.col_pstageID = stageID
            AND tt.col_nextID = relationID
      ORDER BY tt.col_seq, tt.col_taskID;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET max_sp_recursion_depth = 255;

    IF relationID IS NULL THEN
      OPEN taskCursor;
      read_loop:
      LOOP
        FETCH taskCursor INTO curTask, curID, curType, taskDur;
        IF done THEN
          LEAVE read_loop;
        END IF;

        IF curType = 0 THEN -- простая задача
          -- помнить про то, что у простых задач очередость считается в триггере, по максимальной +1
          UPDATE tbl_tasks tt
          SET tt.col_startPlan = dateStart,
            tt.col_endPlan = DATE_ADD(dateStart, INTERVAL tt.col_taskDur DAY)
          WHERE tt.col_taskID = curTask;
          CALL sp_setTaskPlanQuenue(stageID, dateStart, curTask);
        END IF;


      END LOOP;
      CLOSE taskCursor;
    ELSE
      OPEN taskRelationCur;
      read_loop:
      LOOP
        FETCH taskRelationCur INTO curTask, curID, curType, taskDur;
        IF done THEN
          LEAVE read_loop;
        END IF;

        IF curType != 0 THEN -- на самом деле, не особо нужен, оставил на всякий случай
          SELECT
            tt1.col_seq,
            tt1.col_startPlan,
            tt1.col_endPlan INTO curSeq, planStart, planEnd
          FROM tbl_tasks tt1
          WHERE tt1.col_taskID = relationID;

          CASE
            WHEN curType = 1 THEN -- конончание - начало
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq + 1,
              tt.col_startPlan = planEnd,
              tt.col_endPlan = DATE_ADD(planEnd, INTERVAL tt.col_taskDur DAY)
            WHERE tt.col_taskID = curTask;
            WHEN curType = 2 THEN -- начало - начало
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq,
              tt.col_startPlan = planStart,
              tt.col_endPlan = DATE_ADD(planStart, INTERVAL tt.col_taskDur DAY)
            WHERE tt.col_taskID = curTask;
            WHEN curType = 3 THEN -- окончание - окончание
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq,
              tt.col_startPlan = DATE_ADD(planEnd, INTERVAL -tt.col_taskDur DAY),
              tt.col_endPlan = planEnd
            WHERE tt.col_taskID = curTask;
          END CASE;

          CALL sp_setTaskPlanQuenue(stageID, dateStart, curTask);
        END IF;

      END LOOP;
      CLOSE taskRelationCur;
    END IF;
  END