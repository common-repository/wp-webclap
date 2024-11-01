SELECT
    clap_date,
    COUNT(clap_id) AS cnt
FROM
    %s
WHERE
    clap_date BETWEEN '%s' AND '%s'
GROUP BY
    clap_date
ORDER BY
    clap_date DESC