CREATE TABLE `user` (
    `id`         INTEGER AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`  INTEGER NOT NULL,
    `name`       VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL
)