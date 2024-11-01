SELECT
    post_id,
    COUNT(clap_id) AS cnt
FROM
    %s 
GROUP BY
    post_id
ORDER BY
    cnt DESC