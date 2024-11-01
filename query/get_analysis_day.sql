SELECT
    clap_datetime,
    DATE_FORMAT(clap_datetime,'%%H') AS clap_hour,
    post_id,
    ipaddress,
    clap_comment
FROM
    %s
WHERE
    clap_date = '%s'
ORDER BY
    clap_datetime ASC
