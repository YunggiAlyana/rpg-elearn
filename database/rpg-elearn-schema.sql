CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'dosen', 'siswa') DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy user admin (password: admin123)
INSERT INTO users (username, password, role) VALUES (
    'admin', 
    '$2y$10$rH6dbHzq3hvuvLQLwTzZ6O2tku.vQXn7U/MuQx5MkF4XvZTo4pQae', 
    'admin'
);
