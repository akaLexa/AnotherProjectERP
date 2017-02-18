CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateNewTask
BEFORE INSERT
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
            AND col_nextID IS NULL;
      SET new.col_seq = orderSeq;

      IF orderSeq > 1 THEN
        SET new.col_startPlan = NULL;
        SET new.col_endPlan = NULL;
      END IF;
    END IF;
  END