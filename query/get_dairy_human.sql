SELECT
    A.clap_date,
    COUNT(A.ipaddress) AS cnt
FROM (
    SELECT 
        clap_date,
        ipaddress
    FROM
        %s
    WHERE
        clap_date BETWEEN '%s' AND '%s'
    GROUP BY
        clap_date, ipaddress
) AS A
GROUP BY
        clap_date
ORDER BY
    clap_date DESC