CREATE FUNCTION f_getProjectName (projectID int)
  RETURNS varchar(255) charset utf8
  SQL SECURITY INVOKER
  BEGIN
    RETURN (SELECT
              tp.col_projectName
            FROM tbl_project tp
            WHERE tp.col_projectID = projectID);
  END