SELECT
   COUNT(clap_id) AS cnt
FROM
    %s
WHERE
    post_id = %d