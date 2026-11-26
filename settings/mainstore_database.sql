-- Create users table
CREATE TABLE users (
    id            BIGSERIAL     PRIMARY KEY,
    firstname     VARCHAR(100)  NOT NULL,
    lastname      VARCHAR(100)  NOT NULL,
    ident_number  VARCHAR(20)   NOT NULL UNIQUE,
    mobile_phone  VARCHAR(20),
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    url_photo     TEXT,
    birth_date    DATE,
    role          VARCHAR(20)   NOT NULL DEFAULT 'cliente' CHECK (role IN ('cliente', 'admin')),
    status        BOOLEAN       NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    deleted_at    TIMESTAMPTZ
);

-- Select all users from users table 
SELECT * FROM users;

-- Inser a new user into user table
INSERT INTO users (firstname, lastname, ident_number, email, password)  VALUES ('jhonathan', 'Ruiz', '1234543', 'jhon@gmail.com', '123456');