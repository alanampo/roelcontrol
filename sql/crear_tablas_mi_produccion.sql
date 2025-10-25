-- Tabla para registros diarios individuales de trabajadoras
CREATE TABLE IF NOT EXISTS registro_produccion_diario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  item_tipo ENUM('variedad', 'manual') NOT NULL DEFAULT 'variedad',
  fecha DATE NOT NULL,
  turno ENUM('mañana', 'tarde') NOT NULL,
  id_variedad INT NULL,
  descripcion_manual VARCHAR(255) NULL,
  cantidad_plantines INT NOT NULL DEFAULT 0,
  ubicacion_lote VARCHAR(100) NULL,
  observaciones TEXT NULL,
  validado TINYINT(1) DEFAULT 0,
  fecha_validacion DATETIME NULL,
  validado_por INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_variedad) REFERENCES variedades_producto(id),
  FOREIGN KEY (validado_por) REFERENCES usuarios(id),
  INDEX idx_usuario_fecha (id_usuario, fecha),
  INDEX idx_fecha (fecha),
  INDEX idx_item_tipo (item_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para evidencias fotográficas (imágenes comprimidas)
CREATE TABLE IF NOT EXISTS evidencias_produccion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_registro INT NOT NULL,
  ruta_imagen VARCHAR(255) NOT NULL,
  tamano_kb INT NULL,
  fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_registro) REFERENCES registro_produccion_diario(id) ON DELETE CASCADE,
  INDEX idx_registro (id_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para metas semanales por usuario (configuradas por admin)
CREATE TABLE IF NOT EXISTS metas_produccion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  meta_semanal INT NOT NULL DEFAULT 1000,
  fecha_desde DATE NOT NULL,
  activo TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_usuario_activo (id_usuario, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Crear directorio para evidencias
-- NOTA: Ejecutar manualmente: mkdir -p uploads/evidencias && chmod 755 uploads/evidencias
