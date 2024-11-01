CREATE TABLE %s (
    post_id BIGINT unsigned NOT NULL,
    enabled INT(1) NOT NULL,
    button_text VARCHAR(100) NOT NULL,
    page_id VARCHAR(20) NULL,
    UNIQUE KEY id (post_id)
) DEFAULT CHARSET=utf8;