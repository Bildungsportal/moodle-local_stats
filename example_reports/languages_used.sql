/* This report shows the languages used in regard of the user profiles, intended to be shown as pie chart */
SELECT
    ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS id,
    lang AS periodid,
    '' AS subid,
    COUNT(u.id) AS periodvalue,
    0 AS lasttimecreated
FROM {user} u
GROUP by periodid,subid
ORDER by periodid