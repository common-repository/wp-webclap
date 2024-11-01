SELECT
   COUNT(clap_comment) AS cnt
FROM
    %s
WHERE
    post_id = %d
    AND clap_comment IS NOT NULL
    AND clap_comment <> ''