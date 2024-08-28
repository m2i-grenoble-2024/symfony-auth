DROP TABLE IF EXISTS annonce;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(64) NOT NULL
);

CREATE TABLE annonce(
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price FLOAT NOT NULL,
    user_id INT NOT NULL,
    Foreign Key (user_id) REFERENCES user(id) ON DELETE CASCADE
);

INSERT INTO user (email,password,role) VALUES
('admin@admin.com', '$2y$13$8wuW4SJ.HU2Efim3EyQ.qemT/O1M7blxFoEZQzSOEz6iDCNUZccaO', 'ROLE_ADMIN'),
('test@test.com', '$2y$13$8wuW4SJ.HU2Efim3EyQ.qemT/O1M7blxFoEZQzSOEz6iDCNUZccaO', 'ROLE_USER');

INSERT INTO annonce (title,description,price,user_id) VALUES 
('Bike', 'A nice bike', 50, 2),
('Flower pot', 'A big flower pot', 3, 2);
