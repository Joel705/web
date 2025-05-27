-- Crear base de datos si no existe y seleccionarla
CREATE DATABASE IF NOT EXISTS trendify;
USE trendify;

-- Borramos tablas si existieran para evitar conflictos
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS products;

-- Crear tabla de productos con columnas clave
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- ID autoincremental único
    name VARCHAR(100) NOT NULL,                   -- Nombre del producto
    category ENUM('camisas', 'pantalones', 'zapatos', 'accesorios') NOT NULL,  -- Categoría
    price DECIMAL(10,2) NOT NULL,                 -- Precio con dos decimales
    stock INT NOT NULL,                            -- Stock disponible
    image VARCHAR(255) NOT NULL                    -- Nombre de archivo de imagen
);

-- Crear tabla carrito para productos agregados en sesión
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,                       -- ID producto
    quantity INT NOT NULL,                         -- Cantidad agregada
    session_id VARCHAR(100) NOT NULL,              -- ID de sesión para identificar al usuario
    FOREIGN KEY (product_id) REFERENCES products(id) -- Relación con tabla productos
);

-- Tabla para registrar ventas (total y fecha)
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Fecha y hora automática
);

-- Insertar productos de ejemplo para iniciar catálogo
INSERT INTO products (name, category, price, stock, image) VALUES
('Camisa Casual Azul', 'camisas', 350.00, 10, 'camisa1.jpg'),
('Camisa Formal Blanca', 'camisas', 450.00, 8, 'camisa2.jpg'),
('Pantalón Negro Slim', 'pantalones', 480.00, 7, 'pantalon1.jpg'),
('Pantalón Jeans Azul', 'pantalones', 520.00, 15, 'pantalon2.jpg'),
('Zapatos Deportivos', 'zapatos', 1200.00, 5, 'zapatos1.jpg'),
('Zapatos Formales Negros', 'zapatos', 1500.00, 4, 'zapatos2.jpg'),
('Cinturón de Cuero', 'accesorios', 300.00, 20, 'accesorio1.jpg'),
('Gorra Casual', 'accesorios', 180.00, 10, 'accesorio2.jpg');
