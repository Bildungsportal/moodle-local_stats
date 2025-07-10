# moodle-local_stats

## Description

This is a very easy plugin that starts tracking all page visits of any user right after installation.
It tracks the userid, path, param and time of access.

## Reports

Custom reports can be built on top of these records by the site administration in
Website Administration / Plugins / Local Plugins / Reports. If a report is added,
the administrator must provide an sql query. The sql query must not contain certain sql commands, such as
UPDATE, DELETE, TRUNCATE and so on.

The sql query must further provide an auto increment value, a periodid, a subid to the period, a period value and the
latest timecreated-value within that period. For example could you make a report of whith pages in the site-administration
have been used per weeknumber using the query

### Example for Postgre SQL
```
SELECT 
    ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS id,
    CONCAT(
        EXTRACT(YEAR FROM TIMESTAMP 'epoch' + timecreated * INTERVAL '1 second'),
        '-',
        LPAD(EXTRACT(WEEK FROM TIMESTAMP 'epoch' + timecreated * INTERVAL '1 second')::TEXT, 2, '0')
    ) AS periodid,
    substring(
        params
        FROM 'section=([^&]+)'
    ) AS subid,
    COUNT(id) AS periodvalue,
    MAX(timecreated) AS lasttimecreated
FROM bip_local_stats
WHERE 
    timecreated > ?
    AND timecreated < EXTRACT(EPOCH FROM (DATE_TRUNC('week', CURRENT_DATE)))
    and path like '/admin/settings.php'
GROUP by periodid,subid
ORDER by periodid 
```

### Example for MySQL
```
/* DO NOT CHANGE THE FOLLOWING LINES */
SELECT
    ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS id,
/* MODIFY THE FOLLOWING LINES TO CHOOSE THE PERIODS FOR THIS REPORT */
    CONCAT(
        YEAR(FROM_UNIXTIME(timecreated)),
        '-',
        LPAD(WEEK(FROM_UNIXTIME(timecreated)), 2, '0')
    ) AS periodid,
/* MODIFY THE FOLLOWING LINES TO CHOOSE THE SUBIDS AND VALUES FOR THIS REPORT OR PROVIDE `'' AS subid` */
    '' AS subid,
    COUNT(id) AS periodvalue,
/* DO NOT CHANGE THE FOLLOWING LINES */
    MAX(timecreated) AS lasttimecreated
FROM {local_stats}
WHERE
    timecreated > ?
/* MODIFY THE FOLLOWING LINE TO MATCH YOUR PERIOD DURATION */
    AND timecreated < UNIX_TIMESTAMP(CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY)
/* MODIFY THE FOLLOWING LINE TO SELECT THE LOG ITEMS RELEVANT TO YOUR REPORT */
    AND path LIKE '/admin/settings.php'
/* DO NOT CHANGE THE FOLLOWING LINES */
GROUP by periodid,subid
ORDER by periodid
```

### Important information

It is of utmost importance to know the following details:

1. the id field is required by Moodle core, never change the line `ROW_NUMBER() .... as id`!
2. the period id can have any value that you like. It indicates e.g. a month, a weeknumber, a day, ...
3. the subid can be used to distinguish between several values within that period. If you have none, please provide `'' as subid'` instead
4. `COUNT(id) AS periodvalue` provides the actual value to be tracked. Never change this line!
5. `MAX(timecreated) AS lasttimecreated` is required, so that a period is not calculated more than once. Never change this line!
6. `WHERE timecreated > ?` is required and will automatically used. Never change this line!
7. `AND timecreated < ...` prevents that a value for the current period is calculated. Always adapt this line to the period that you use!
8. `AND path LIKE ...` can be replaced by any select requirement that identifiers the records you are interested in.
9. `GROUP by period,subid` and `ORDER BY period` is required. Never change this line!
