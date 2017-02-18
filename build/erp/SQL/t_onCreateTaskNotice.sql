CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateTaskNotice
AFTER INSERT
  ON tbl_tasks
FOR EACH ROW
  BEGIN
    IF new.col_StatusID != 5 THEN

      IF new.col_StatusID = 1 THEN
        -- оповещение куратора о задаче
        IF new.col_curatorID IS NOT NULL
           AND new.col_curatorID != new.col_respID THEN
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (5, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;
      END IF;

      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 4 THEN       -- оповещение ответственного о новой задаче
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (6, new.col_taskID, new.col_respID, new.col_taskName);
        END IF;
      END IF;
    END IF;
  END