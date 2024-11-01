CREATE TABLE %s (
    clap_id	BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    clap_date DATE NOT NULL,
    clap_datetime DATETIME NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    ipaddress VARCHAR(15) NOT NULL,
    clap_name TEXT,
    clap_comment TEXT,
    PRIMARY KEY (clap_id),
    KEY idx_webclap_comments_date (clap_date),
    KEY idx_webclap_comments_datetime (clap_datetime),
    KEY idx_webclap_comments_postid (post_id),
    KEY idx_webclap_comments_ipaddress (ipaddress),
    KEY idx_webclap_comments_comment (clap_comment(30))
) DEFAULT CHARSET=utf8;