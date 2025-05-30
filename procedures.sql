--rozszerzenie pgcrypto

CREATE EXTENSION IF NOT EXISTS pgcrypto;

--register_user

CREATE OR REPLACE FUNCTION register_user(
    _email VARCHAR,
    _username VARCHAR,
    _password VARCHAR
) RETURNS INTEGER AS $$
DECLARE
    _hashed_password VARCHAR;
    _id_user INTEGER;
BEGIN
    IF EXISTS (SELECT 1 FROM users WHERE email = _email) THEN
        RAISE EXCEPTION 'EMAIL_TAKEN';
    END IF;

    _hashed_password := crypt(_password, gen_salt('bf'));

    INSERT INTO users (email, username, password)
    VALUES (_email, _username, _hashed_password)
    RETURNING id_user INTO _id_user;

    INSERT INTO activity (id_user) VALUES (_id_user);

    RETURN _id_user;
END;
$$ LANGUAGE plpgsql;

--login_user

CREATE OR REPLACE FUNCTION login_user(
    _email VARCHAR,
    _password VARCHAR
) RETURNS TABLE(id_user INT, username VARCHAR, id_activity INT) AS $$
DECLARE
    _hashed VARCHAR;
BEGIN
    SELECT u.id_user, u.username, u.password
    INTO id_user, username, _hashed
    FROM users u
    WHERE u.email = _email;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'EMAIL_NOT_FOUND';
    END IF;

    IF NOT crypt(_password, _hashed) = _hashed THEN
        RAISE EXCEPTION 'INVALID_PASSWORD';
    END IF;

    INSERT INTO activity (id_user) VALUES (id_user)
    RETURNING activity.id_activity INTO login_user.id_activity;

    RETURN NEXT;
    RETURN;
END;
$$ LANGUAGE plpgsql;

--logout_user

CREATE OR REPLACE FUNCTION logout_user(
    _id_activity INT
) RETURNS VOID AS $$
BEGIN
    UPDATE activity
    SET logout = NOW()
    WHERE id_activity = _id_activity;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'ACTIVITY_NOT_FOUND';
    END IF;

END;
$$ LANGUAGE plpgsql;

--get_available_albums

CREATE OR REPLACE FUNCTION get_available_albums()
RETURNS TABLE (
    id_album INT,
    title VARCHAR,
    artist_name VARCHAR,
    cover_path VARCHAR,
    price DECIMAL(10,2),
    available_copies INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        a.id_album,
        a.title,
        ar.artist_name AS artist_name,
        a.cover_path,
        a.price,
        COUNT(c.id_copy)::INT AS available_copies
    FROM albums a
    JOIN artists ar ON a.id_artist = ar.id_artist
    JOIN copies c ON a.id_album = c.id_album
    LEFT JOIN orders o ON c.id_copy = o.id_copy AND o.status != 'anulowane'
    WHERE o.id_order IS NULL
    GROUP BY a.id_album, a.title, ar.artist_name, a.cover_path, a.price
    HAVING COUNT(c.id_copy) > 0;
END;
$$ LANGUAGE plpgsql;

--make_order

CREATE OR REPLACE FUNCTION make_order(p_id_user INTEGER, p_id_album INTEGER)
RETURNS VOID AS $$
DECLARE
    v_id_copy INT;
BEGIN
    -- Znajdź wolną kopię albumu
    SELECT c.id_copy INTO v_id_copy
    FROM copies c
    LEFT JOIN orders o ON c.id_copy = o.id_copy AND o.status != 'anulowane'
    WHERE c.id_album = p_id_album AND o.id_order IS NULL
    LIMIT 1;

    IF v_id_copy IS NULL THEN
        RAISE EXCEPTION 'Brak dostępnych kopii albumu o ID %', p_id_album;
    END IF;

    -- Wstaw zamówienie
    INSERT INTO orders(id_user, id_copy, order_date, status)
    VALUES (p_id_user, v_id_copy, NOW(), 'złożone');
END;
$$ LANGUAGE plpgsql;

--get_user_orders

CREATE OR REPLACE FUNCTION get_user_orders(_id_user INT)
RETURNS TABLE (
    title VARCHAR,
    artist_name VARCHAR,
    price DECIMAL(10,2),
    order_date TIMESTAMP,
    status VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        a.title,
        ar.artist_name,
        a.price,
        o.order_date,
        o.status
    FROM orders o
    JOIN copies c ON o.id_copy = c.id_copy
    JOIN albums a ON c.id_album = a.id_album
    JOIN artists ar ON a.id_artist = ar.id_artist
    WHERE o.id_user = _id_user
    ORDER BY o.order_date DESC;
END;
$$ LANGUAGE plpgsql;
