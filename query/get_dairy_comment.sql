SELECT
    clap_date,
    COUNT(clap_comment) AS cnt
FROM
    %s
WHERE
    clap_date BETWEEN '%s' AND '%s'
    AND clap_comment IS NOT NULL
    AND clap_comment <> ''
GROUP BY
    clap_date
ORDER BY
    clap_date DESC
