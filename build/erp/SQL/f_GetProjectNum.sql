CREATE FUNCTION f_GetProjectNum (projectID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
  COMMENT 'номер по id проекта'
  BEGIN

    RETURN (SELECT
              tp.col_pnID
            FROM tbl_project tp
            WHERE tp.col_projectID = projectID);
  END