SELECT
   post_id,
   clap_comment
FROM
    %s
WHERE
    clap_comment IS NOT NULL
    AND clap_comment <> ''
ORDER BY
    clap_datetime DESC
LIMIT 0, %d
