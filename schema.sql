CREATE TABLE coworking_spaces (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    region VARCHAR(100) NOT NULL,
    address TEXT,
    email VARCHAR(255),
    tel VARCHAR(50),
    website VARCHAR(255),
    notes TEXT
);
