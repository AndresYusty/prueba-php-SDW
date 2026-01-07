-- =====================================================
-- Script de Base de Datos para Formulario de Registro
-- Base de datos: formulario_db
-- =====================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `formulario_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE `formulario_db`;

-- Eliminar tabla si existe (para recrear desde cero)
DROP TABLE IF EXISTS `usuarios`;

-- Crear tabla de usuarios
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID único del usuario',
  `tipo_documento` VARCHAR(2) NOT NULL COMMENT 'Tipo de documento: CC, CE, PA, TI',
  `documento` VARCHAR(20) NOT NULL COMMENT 'Número de documento',
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre completo del usuario',
  `edad` INT(3) NOT NULL COMMENT 'Edad del usuario (mínimo 18)',
  `genero` VARCHAR(2) NOT NULL COMMENT 'Género: M, F, O, PN',
  `preferencias` JSON NOT NULL COMMENT 'Preferencias almacenadas como JSON',
  `latitud` DECIMAL(10, 8) DEFAULT NULL COMMENT 'Latitud del dispositivo',
  `longitud` DECIMAL(11, 8) DEFAULT NULL COMMENT 'Longitud del dispositivo',
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `fecha_actualizacion` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipo_documento` (`tipo_documento`, `documento`) COMMENT 'Índice único para evitar duplicados',
  KEY `idx_documento` (`documento`) COMMENT 'Índice para búsquedas por documento',
  KEY `idx_nombre` (`nombre`) COMMENT 'Índice para búsquedas por nombre',
  KEY `idx_fecha_creacion` (`fecha_creacion`) COMMENT 'Índice para ordenar por fecha'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Tabla para almacenar información de usuarios del formulario';

-- =====================================================
-- Datos de ejemplo (opcional)
-- =====================================================

-- Insertar algunos registros de ejemplo
INSERT INTO `usuarios` 
(`tipo_documento`, `documento`, `nombre`, `edad`, `genero`, `preferencias`, `latitud`, `longitud`) 
VALUES 
('CC', '1234567890', 'Juan Pérez', 25, 'M', '["deportes", "tecnologia", "viajes"]', 4.609710, -74.081750),
('CE', '9876543210', 'María García', 30, 'F', '["musica", "lectura", "arte"]', 4.609710, -74.081750),
('PA', '1122334455', 'Carlos Rodríguez', 28, 'M', '["cocina", "cine", "tecnologia"]', NULL, NULL);

-- =====================================================
-- Consultas útiles para verificar
-- =====================================================

-- Ver todos los usuarios
-- SELECT * FROM usuarios;

-- Ver usuarios con preferencias formateadas
-- SELECT 
--     id,
--     tipo_documento,
--     documento,
--     nombre,
--     edad,
--     genero,
--     JSON_PRETTY(preferencias) as preferencias,
--     latitud,
--     longitud,
--     fecha_creacion
-- FROM usuarios;

-- Contar usuarios por género
-- SELECT genero, COUNT(*) as total 
-- FROM usuarios 
-- GROUP BY genero;

-- =====================================================
-- Notas importantes:
-- =====================================================
-- 1. El campo 'preferencias' es de tipo JSON, compatible con MySQL 5.7+
-- 2. El índice único (tipo_documento, documento) previene duplicados
-- 3. Las coordenadas son opcionales (pueden ser NULL)
-- 4. La edad tiene validación de mínimo 18 años en la aplicación
-- 5. Todos los campos de texto usan UTF-8 para soportar caracteres especiales
-- =====================================================

