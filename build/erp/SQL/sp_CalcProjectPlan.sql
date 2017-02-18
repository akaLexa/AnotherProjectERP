CREATE PROCEDURE sp_CalcProjectPlan (IN projectID int, IN dateStart date)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'расчет плана проекта'
  BEGIN

    DECLARE currentStage int;
    DECLARE currentDuration int;
    DECLARE currentEndDate date;
    DECLARE i int DEFAULT 0;
    DECLARE done int DEFAULT FALSE;

    DECLARE cu_Work CURSOR FOR

      SELECT
        tps.col_pstageID,
        tps.col_duration
      FROM tbl_project_stage tps,
        tbl_hb_project_stage thps,
        tbl_hb_status ths
      WHERE tps.col_projectID = projectID
            AND tps.col_statusID = 5
            AND thps.col_StageID = tps.col_stageID
            AND ths.col_StatusID = tps.col_statusID
      ORDER BY tps.col_seq;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cu_Work;

    read_loop:
    LOOP
      FETCH cu_Work INTO currentStage, currentDuration;
      IF done THEN
        LEAVE read_loop;
      END IF;

      SET currentEndDate = DATE_ADD(dateStart, INTERVAL currentDuration DAY);
      UPDATE tbl_project_stage tps
      SET tps.col_dateStartPlan = dateStart,
        tps.col_dateEndPlan = currentEndDate
      WHERE tps.col_pstageID = currentStage;
      CALL sp_setTaskPlanQuenue(currentStage, dateStart, NULL);
      SET dateStart = currentEndDate;

    END LOOP;
    CLOSE cu_Work;
  END
