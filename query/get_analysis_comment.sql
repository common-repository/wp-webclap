SELECT
    *
FROM
    %s
WHERE
    clap_date = '%s'
    AND clap_comment IS NOT NULL
    AND clap_comment <> ''
ORDER BY
    clap_datetime DESC
