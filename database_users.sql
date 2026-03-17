-- Users and privileges (run after 00_init.sql)

-- App user (least privilege)
CREATE USER IF NOT EXISTS 'curtain'@'%' IDENTIFIED BY 'curtain123';
ALTER USER 'curtain'@'%' IDENTIFIED BY 'curtain123' ACCOUNT UNLOCK;
GRANT SELECT, INSERT, UPDATE, DELETE ON curtain_db.* TO 'curtain'@'%';

-- Admin user for maintenance (use in phpMyAdmin)
CREATE USER IF NOT EXISTS 'curtain_admin'@'%' IDENTIFIED BY 'CurtainAdmin!2026';
ALTER USER 'curtain_admin'@'%' IDENTIFIED BY 'CurtainAdmin!2026' ACCOUNT UNLOCK;
GRANT ALL PRIVILEGES ON *.* TO 'curtain_admin'@'%' WITH GRANT OPTION;

-- Keep root only for localhost and lock remote root
CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY 'RootOnly!2026';
ALTER USER 'root'@'localhost' IDENTIFIED BY 'RootOnly!2026' ACCOUNT UNLOCK;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
DELETE FROM mysql.user WHERE user = 'root' AND host != 'localhost';

FLUSH PRIVILEGES;
