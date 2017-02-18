CREATE FUNCTION f_getNextTaskSDR (stageID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает следующую СДР для задачи в плане'
  BEGIN
    RETURN (SELECT
              MAX(tt.col_seq) + 1
            FROM tbl_tasks tt
            WHERE tt.col_pstageID = stageID);
  END