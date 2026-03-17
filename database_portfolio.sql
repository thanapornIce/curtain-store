-- Portfolio items table
CREATE TABLE IF NOT EXISTS portfolio_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  location VARCHAR(150) NULL,
  cover_image VARCHAR(255) NOT NULL,
  detail_url VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO portfolio_items (title, category, location, cover_image, detail_url, sort_order, is_active)
VALUES
  ('บ้านลูกค้า 1', 'ม่านจีบ', 'กรุงเทพฯ', 'images/p1.jpg', 'portfolio-detail.html?work=1', 1, 1),
  ('บ้านลูกค้า 2', 'ม่านลอนรางเทป', 'นนทบุรี', 'images/p2.jpg', 'portfolio-detail.html?work=2', 2, 1),
  ('บ้านลูกค้า 3', 'ม่านพับ', 'ปทุมธานี', 'images/p3.jpg', 'portfolio-detail.html?work=3', 3, 1);