CREATE
  DEFINER = CURRENT_USER
TRIGGER onUpdateTask
BEFORE UPDATE
  ON tbl_tasks
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int DEFAULT 0;
    DECLARE endPlan datetime;

    /*
    выставление СДР у задачи без связи
    рассчет даты старта/завершения в случае, если это не 1 задача
    */
    IF new.col_nextID IS NULL
       AND new.col_StatusID = 5 THEN
      SET new.col_bonding = 0;

      SELECT
        COALESCE(MAX(col_seq), 0) + 1 INTO orderSeq
      FROM tbl_tasks
      WHERE col_pstageID = NEW.col_pstageID
            AND col_nextID IS NULL
            AND col_taskID != new.col_taskID
            AND COALESCE(new.col_seq, 0) > col_seq;

      IF orderSeq > 1 THEN
        SET new.col_startPlan = NULL;
        SET new.col_endPlan = NULL;
      ELSE
        SELECT
          col_dateStartPlan INTO endPlan
        FROM tbl_project_stage
        WHERE col_pstageID = new.col_pstageID;
        SET new.col_startPlan = endPlan;
        SET new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY);
      END IF;

    END IF;

    -- создание нотиса в журнал событий
    IF new.col_StatusID != 5
       AND new.col_StatusID != old.col_StatusID THEN
      -- курируемая задача
      IF new.col_curatorID IS NOT NULL
         AND new.col_curatorID != new.col_respID THEN
        IF new.col_StatusID = 1 THEN -- старт
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (5, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (13, new.col_taskID, new.col_curatorID, CONCAT(new.col_taskName, ' причина: ', new.col_failDes));
        END IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (12, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 4
           AND old.col_StatusID = 2 THEN
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (15, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;
      END IF;

      -- оповещение инициатора
      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 1 THEN --  старт задачи
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (3, new.col_taskID, new.col_initID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (14, new.col_taskID, new.col_initID, CONCAT(new.col_taskName, ' причина: ', new.col_failDes));
        END IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (4, new.col_taskID, new.col_initID, new.col_taskName);
        END IF;

      END IF;

      -- перезапуск задачи
      IF new.col_StatusID = 4
         AND old.col_StatusID = 2 THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (16, new.col_taskID, new.col_respID, new.col_taskName);
      END IF;
    END IF;

  END
