-- Database usato dalla suite di test.
CREATE DATABASE IF NOT EXISTS pescheria_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON pescheria_test.* TO 'pescheria'@'%';
FLUSH PRIVILEGES;
