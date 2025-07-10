/* This report shows the amount of pages viewed per week */
SELECT
    ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS id,
    CONCAT(
        EXTRACT(YEAR FROM TIMESTAMP 'epoch' + timecreated * INTERVAL '1 second'),
        '-',
        LPAD(EXTRACT(WEEK FROM TIMESTAMP 'epoch' + timecreated * INTERVAL '1 second')::TEXT, 2, '0')
    ) AS periodid,
    '' AS subid,
    COUNT(id) AS periodvalue,
    MAX(timecreated) AS lasttimecreated
FROM {local_stats}
WHERE
        timecreated > EXTRACT(EPOCH FROM date_trunc('week', TIMESTAMP WITH TIME ZONE 'epoch' + ? * INTERVAL '1 second'))
    AND timecreated < EXTRACT(EPOCH FROM (date_trunc('week', CURRENT_DATE) - INTERVAL '1 second'))
GROUP by periodid,subid
ORDER by periodid
