--Structure of database MusicStore

CREATE TABLE users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE admins (
    id_admin SERIAL PRIMARY KEY,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE mods (
    id_mod SERIAL PRIMARY KEY,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE chats (
    id_chat SERIAL PRIMARY KEY,
    id_user INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE chat_history (
    id_history SERIAL PRIMARY KEY,
    id_chat INT NOT NULL,
    prompt TEXT NOT NULL,
    completion TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chat) REFERENCES chats(id_chat) ON DELETE CASCADE
);

CREATE TABLE activity (
    id_activity SERIAL PRIMARY KEY,
    id_user INT NOT NULL,
    logged TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout TIMESTAMP NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE artists (
    id_artist SERIAL PRIMARY KEY,
    artist_name VARCHAR(255) NOT NULL
);

CREATE TABLE albums (
    id_album SERIAL PRIMARY KEY,
    id_artist INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    release_date DATE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cover_path VARCHAR(255) NOT NULL DEFAULT 'albums/default_cover.png',
    FOREIGN KEY (id_artist) REFERENCES artists(id_artist) ON DELETE CASCADE
);

CREATE TABLE copies (
    id_copy SERIAL PRIMARY KEY,
    id_album INT NOT NULL,
    FOREIGN KEY (id_album) REFERENCES albums(id_album) ON DELETE CASCADE
);

CREATE TABLE orders (
    id_order SERIAL PRIMARY KEY,
    id_user INT NOT NULL,
    id_copy INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_copy) REFERENCES copies(id_copy) ON DELETE CASCADE
);

-- Sample data for the MusicStore database

INSERT INTO artists (artist_name) VALUES
('Radiohead'),
('Beyoncé'),
('Daft Punk'),
('Adele'),
('Arctic Monkeys');

INSERT INTO albums (id_artist, title, release_date, price, cover_path) VALUES
(1, 'OK Computer', '1997-06-16', 49.99, 'albums/default_cover.png'),
(1, 'In Rainbows', '2007-10-10', 39.99, 'albums/default_cover.png'),
(2, 'Lemonade', '2016-04-23', 59.99, 'albums/default_cover.png'),
(2, 'Renaissance', '2022-07-29', 69.99, 'albums/default_cover.png'),
(3, 'Random Access Memories', '2013-05-17', 54.99, 'albums/default_cover.png'),
(4, '21', '2011-01-24', 44.99, 'albums/default_cover.png'),
(5, 'AM', '2013-09-09', 39.99, 'albums/default_cover.png');

INSERT INTO copies (id_album) VALUES
(1), (1), (1);

INSERT INTO copies (id_album) VALUES
(2), (2);

INSERT INTO copies (id_album) VALUES
(3), (3), (3);

INSERT INTO copies (id_album) VALUES
(4), (4);

INSERT INTO copies (id_album) VALUES
(5), (5), (5), (5);

INSERT INTO copies (id_album) VALUES
(6), (6), (6);

INSERT INTO copies (id_album) VALUES
(7), (7);
