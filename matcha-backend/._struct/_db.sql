CREATE TABLE IF NOT EXISTS tbl_users
(
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `username`          VARCHAR(16) UNIQUE NOT NULL,
    `email`             VARCHAR(120) UNIQUE NOT NULL,
    `password`          TEXT NOT NULL,
    `firstname`         VARCHAR(90) NOT NULL,
    `lastname`          VARCHAR(90) NOT NULL,
    `gender`            ENUM('none','male','female') NOT NULL DEFAULT 'none',
    `date_of_birth`     DATE,
    `sexual_preference` ENUM('all','male','female') NOT NULL DEFAULT 'all',
    `biography`         TEXT,
    `address`           TEXT,
    `token`             TEXT,
    `salt`              TEXT NOT NULL,
    `date_updated`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_user_registrations
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`       VARCHAR(16) UNIQUE NOT NULL,
    `email`         VARCHAR(120) UNIQUE NOT NULL,
    `password`      TEXT NOT NULL,
    `firstname`     VARCHAR(90) NOT NULL,
    `lastname`      VARCHAR(90) NOT NULL,
    `date_of_birth` DATE,
    `salt`          TEXT,
    `token`         TEXT,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_user_images
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL REFERENCES tbl_users(id),
    `code`          INT NOT NULL,
    `url`           TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

/*
CREATE TABLE IF NOT EXISTS tbl_user_notifications
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_to`    INT NOT NULL REFERENCES tbl_users(id),
    `user_id_from`  INT NOT NULL REFERENCES tbl_users(id),
    `dsc`           TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
*/

/*
CREATE TABLE IF NOT EXISTS tbl_visit_history
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`  INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`    INT NOT NULL REFERENCES tbl_users(id),
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
);
*/

CREATE TABLE IF NOT EXISTS tbl_user_history
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`  INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`    INT NOT NULL,
    `action`        ENUM('connect','unconnect','visit', 'login', 'message', 'report', 'block') NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
);

CREATE TABLE IF NOT EXISTS tbl_login_session
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL REFERENCES tbl_users(id),
    `session`       VARCHAR(255) UNIQUE NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_user_connections(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`  INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`    INT NOT NULL REFERENCES tbl_users(id),
    `status`        INT NOT NULL DEFAULT 0,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_user_messages(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`  INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`    INT NOT NULL REFERENCES tbl_users(id),
    `message`       TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_interests
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `tag`           TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP   
);

CREATE TABLE IF NOT EXISTS tbl_user_interests
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL REFERENCES tbl_users(id),
    `interest_id`   INT NOT NULL REFERENCES tbl_interests(id),
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP   
);

CREATE TABLE IF NOT EXISTS tbl_user_report(
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`      INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`        INT NOT NULL REFERENCES tbl_users(id),
    `description`       TEXT,
    `date_updated`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_user_block(
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `user_id_from`      INT NOT NULL REFERENCES tbl_users(id),
    `user_id_to`        INT NOT NULL REFERENCES tbl_users(id),
    `date_updated`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_access
(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `app`           TEXT NOT NULL,
    `token`         TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP   
);

-- Not sure with this... ---

CREATE TABLE IF NOT EXISTS tbl_user_locations(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL REFERENCES tbl_users(id),
    `location`      TEXT NOT NULL,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); 

/* default table

CREATE TABLE IF NOT EXISTS tbl_(
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `date_updated`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

*/