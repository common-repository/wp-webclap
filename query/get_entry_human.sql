SELECT
    COUNT(A.ipaddress) AS cnt
FROM (
    SELECT
       ipaddress
    FROM
        %s
    WHERE
        post_id = %d
    GROUP BY
        ipaddress
) AS A   