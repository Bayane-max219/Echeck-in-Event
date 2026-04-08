-- Initialisation automatique de la base Echeck-in
CREATE DATABASE IF NOT EXISTS `echeck_in`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `echeck_in`.* TO 'echeck_user'@'%';
FLUSH PRIVILEGES;
