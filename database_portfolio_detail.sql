-- Extend portfolio_items and add portfolio_images
ALTER TABLE portfolio_items
  ADD COLUMN IF NOT EXISTS description TEXT NULL,
  ADD COLUMN IF NOT EXISTS duration_days INT NULL,
  ADD COLUMN IF NOT EXISTS spaces VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS video_url VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS portfolio_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  portfolio_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_portfolio_images_portfolio (portfolio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed detail fields
UPDATE portfolio_items
SET description = 'โครงการติดตั้งผ้าม่านสำหรับบ้านพักอาศัย ออกแบบให้รับแสงธรรมชาติและช่วยควบคุมแสงได้อย่างลงตัว',
    duration_days = 7,
    spaces = 'ห้องนอน / ห้องนั่งเล่น',
    video_url = 'videos/project-1.mp4'
WHERE id = 1;

UPDATE portfolio_items
SET description = 'ติดตั้งม่านลอนรางเทป เน้นความโปร่งสบายและการเปิด-ปิดที่ลื่นไหล',
    duration_days = 5,
    spaces = 'ห้องรับแขก',
    video_url = 'videos/project-1.mp4'
WHERE id = 2;

UPDATE portfolio_items
SET description = 'งานติดตั้งม่านพับสำหรับห้องทำงาน เน้นความเรียบหรูและประหยัดพื้นที่',
    duration_days = 4,
    spaces = 'ห้องทำงาน / ห้องอ่านหนังสือ',
    video_url = 'videos/project-1.mp4'
WHERE id = 3;

-- Seed gallery images
INSERT INTO portfolio_images (portfolio_id, image_url, sort_order)
VALUES
  (1, 'images/p1.jpg', 1),
  (1, 'images/p1.1.jpg', 2),
  (1, 'images/p1.2.jpg', 3),
  (1, 'images/p1.3.jpg', 4),
  (1, 'images/p1.4.jpg', 5),
  (2, 'images/p2.jpg', 1),
  (2, 'images/gallery-01.jpg', 2),
  (2, 'images/gallery-02.jpg', 3),
  (2, 'images/gallery-03.jpg', 4),
  (2, 'images/gallery-04.jpg', 5),
  (3, 'images/p3.jpg', 1),
  (3, 'images/gallery-05.jpg', 2),
  (3, 'images/gallery-06.jpg', 3),
  (3, 'images/gallery-07.jpg', 4),
  (3, 'images/gallery-01.jpg', 5);