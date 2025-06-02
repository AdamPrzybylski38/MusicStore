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
INSERT INTO users (username, email, password)
VALUES ('admin', 'admin@email.com', '$2a$06$yj5f3YSAshFeYphvcHv5Z.Uw9DxMJFVMKEiokI37/InJTM8mbCZuC');

INSERT INTO users (username, email, password)
VALUES ('moderator', 'mod@email.com', '$2a$06$3PNy1HacQE1XZ3WlH0sgJu5RqTBwoTu40iPGF1miVH5YpC3FTkYh2');

INSERT INTO admins (id_user)
(SELECT id_user FROM users WHERE email = 'admin@email.com');

INSERT INTO mods (id_user)
(SELECT id_user FROM users WHERE email = 'mod@email.com');

INSERT INTO artists (artist_name) VALUES
('Radiohead'),
('Pink Floyd'),
('King Crimson'),
('My Bloody Valentine'),
('The Cure'),
('The Beatles'),
('Talking Heads'),
('David Bowie'),
('XavlegbmaofffassssitimiwoamndutroabcwapwaeiippohfffX'),
('The Smiths'),
('Joy Division'),
('Depeche Mode'),
('Slowdive'),
('Bathory'),
('Darkthrone'),
('Furia'),
('Mgła'),
('Deafheaven'),
('Trhä');

INSERT INTO albums (id_artist, title, release_date, price, cover_path) VALUES
(1, 'OK Computer', '1997-06-16', 49.99, 'albums/Radiohead - OK Computer, Cover art.webp'),
(2, 'In Rainbows', '2007-10-10', 39.99, 'albums/Radiohead - In Rainbows, Cover art.webp'),
(2, 'Wish You Were Here', '1975-09-12', 59.99, 'albums/Pink Floyd - Wish You Were Here, Cover art.webp'),
(2, 'The Dark Side of the Moon', '1973-03-23', 79.99, 'albums/Pink Floyd - The Dark Side of the Moon, Cover art.webp'),
(3, 'In the Court of the Crimson King', '1969-10-10', 69.99, 'albums/King Crimson - In the Court of the Crimson King, Cover art.webp'),
(4, 'Loveless', '1991-11-11', 69.99, 'albums/My Bloody Valentine - Loveless, Cover art.webp'),
(5, 'Disintegration', '1989-05-2', 89.99, 'albums/The Cure - Disintegration, Cover art.webp'),
(6, 'Abbey Road', '1969-09-26', 129.99, 'albums/The Beatles - Abbey Road, Cover art.webp'),
(7, 'Remain in Light', '1980-10-8', 99.99, 'albums/Talking Heads - Remain in Light, Cover art.webp'),
(8, 'The Rise and Fall of Ziggy Stardust and the Spiders from Mars', '1972-06-16', 89.99, 'albums/David Bowie - The Rise and Fall of Ziggy Stardust and The Spiders From Mars, Cover art.webp'),
(9, 'Gore', '2016-07-31', 99.99, 'albums/xavlegbmaofffassssitimiwoamndutroabcwapwaeiippohfffx-gore-Cover-Art.jpg'),
(10, 'The Queen Is Dead', '1986-06-16', 59.99, 'albums/The Smiths - The Queen Is Dead, Cover art.webp'),
(11, 'Unknown Pleasures', '1979-06-15', 129.99, 'albums/Joy Division - Unknown Pleasures, Cover art.webp'),
(12, 'Black Celebration', '1986-03-17', 99.99, 'albums/5806790.jpeg'),
(13, 'Souvlaki', '1993-06-1', 109.99, 'albums/Slowdive - Souvlaki, Cover art.webp'),
(14, 'Blood Fire Death', '1988-10-8', 89.99, 'albums/Bathory - Blood Fire Death, Cover art.webp'),
(15, 'A Blaze in the Northern Sky', '1992-02-26', 119.99, 'albums/Darkthrone - A Blaze in the Northern Sky, Cover art.webp'),
(16, 'Księżyc milczy luty', '2016-11-14', 89.99, 'albums/Furia - Księżyc milczy luty, Cover art.webp'),
(17, 'Exercises in Futility', '2015-08-7', 99.99, 'albums/Mgła - Exercises in Futility, Cover art.webp'),
(18, 'Sunbather', '2013-06-11', 89.99, 'albums/Deafheaven - Sunbather, Cover art.webp'),
(18, 'Lonly People With Power', '2025-03-28', 119.99, 'albums/Deafheaven - Lonely People With Power, Cover art.webp'),
(19, 'faj den EnΩëtonëghappan nvona Tóvarba dëhajnva ëfpalte∫ eh yënáº£les §anënbe cetmac eh den léhams selb`ºe nêbam`o∫nëb ◊u∫an d‡éf§', '2024-12-26', 79.99, 'albums/Trhä-Cover-Art.jpg');

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
(6), (6), (6), (6);

INSERT INTO copies (id_album) VALUES
(7), (7), (7);

INSERT INTO copies (id_album) VALUES
(8), (8), (8), (8);

INSERT INTO copies (id_album) VALUES
(9), (9), (9), (9), (9);

INSERT INTO copies (id_album) VALUES
(10), (10), (10), (10), (10);

INSERT INTO copies (id_album) VALUES
(11), (11);

INSERT INTO copies (id_album) VALUES
(12), (12), (12), (12);

INSERT INTO copies (id_album) VALUES
(13), (13), (13), (13), (13);

INSERT INTO copies (id_album) VALUES
(14), (14), (14);

INSERT INTO copies (id_album) VALUES
(15), (15), (15), (15);

INSERT INTO copies (id_album) VALUES
(16), (16), (16), (16), (16);

INSERT INTO copies (id_album) VALUES
(17), (17), (17);

INSERT INTO copies (id_album) VALUES
(18), (18), (18), (18);

INSERT INTO copies (id_album) VALUES
(19), (19), (19);

INSERT INTO copies (id_album) VALUES
(20), (20), (20), (20);

INSERT INTO copies (id_album) VALUES
(21), (21), (21);

INSERT INTO copies (id_album) VALUES
(22), (22), (22);
