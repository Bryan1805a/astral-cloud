CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','staff','admin') DEFAULT 'user',
  tier ENUM('silver','gold','diamond') DEFAULT 'silver',
  total_spent DECIMAL(15,2) DEFAULT 0,
  is_locked TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add more table here

-- Create default account (password: admin123)
INSERT IGNORE INTO users (name, email, password, role)
VALUES ('Admin', 'admin@astralcloud.com',
        '$2y$12$5SkJlr1TQFU0QBClNdveJeahtWFiHEs0kwEM0vhS1/nTbNObceGXe', 'admin');