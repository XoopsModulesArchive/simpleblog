-- $Id: mysql.sql,v 1.1 2006/03/20 16:19:18 mikhail Exp $

-- blog information table
-- uid :
--    xoops uid
-- blog_permission:
--    0: anyone can read, anyone can write comment
--    1: anyone can read, user can write comment
--    3: user can read, user can write comment
-- last_update:
--    update time
CREATE TABLE simpleblog_info (
    uid             INT(5) UNSIGNED NOT NULL DEFAULT '0',
    title           VARCHAR(200) BINARY,
    blog_permission TINYINT(1)      NOT NULL DEFAULT '0',
    last_update     TIMESTAMP       NOT NULL,
    PRIMARY KEY (uid)
)
    ENGINE = ISAM;

-- blog data table
-- uid :
--     xoops uid
-- blog_date :
--     date of blog
-- title :
--     title of blog
-- post_text :
--     blog data
-- alter table xoops_simpleblog add last_update timestamp not null;
CREATE TABLE simpleblog (
    uid         INT(5) UNSIGNED NOT NULL DEFAULT '0',
    blog_date   DATE            NOT NULL,
    title       VARCHAR(200),
    post_text   TEXT,
    last_update TIMESTAMP       NOT NULL,
    PRIMARY KEY (uid, blog_date)
)
    ENGINE = ISAM;


-- blog comment table
-- uid :
--     xoops uid
-- blog_date :
--     date of blog
-- comment_id :
--     sequential comment id. 
-- comment_uid :
--     uid of comment user. set value to 0 if guest user
-- comment_name :
--     guest user name. 
-- post_text :
--     comment data
CREATE TABLE simpleblog_comment (
    uid          INT(5) UNSIGNED NOT NULL DEFAULT '0',
    blog_date    DATE            NOT NULL,
    comment_id   INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    comment_uid  INT(5) UNSIGNED NOT NULL DEFAULT '0',
    comment_name VARCHAR(200),
    post_text    TEXT,
    create_date  TIMESTAMP       NOT NULL,
    KEY (uid, blog_date),
    PRIMARY KEY (comment_id)
)
    ENGINE = ISAM;

CREATE TABLE simpleblog_application (
    uid         INT(5) UNSIGNED NOT NULL,
    title       VARCHAR(200) BINARY,
    permission  TINYINT(1)      NOT NULL,
    create_date INT(10)         NOT NULL,
    PRIMARY KEY (uid)
)
    ENGINE = ISAM;


CREATE TABLE simpleblog_trackback (
    uid    INT(5) UNSIGNED NOT NULL,
    t_date DATE            NOT NULL,
    count  INT(8) UNSIGNED,
    title  VARCHAR(250),
    url    TEXT,
    KEY (uid, t_date)
)
    ENGINE = ISAM;
