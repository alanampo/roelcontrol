CREATE TABLE `reservas` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `observaciones` TEXT,
  `id_usuario` int(11) NOT NULL,
  FOREIGN KEY (id_cliente) REFERENCES clientes(ID_CLIENTE),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `reservas_productos` 
ADD COLUMN `id_reserva` INT(11) AFTER `id`,
ADD CONSTRAINT `fk_reservas_productos_reservas` 
FOREIGN KEY (`id_reserva`) REFERENCES `reservas`(`id`);

ALTER TABLE `entregas_stock`
ADD COLUMN `id_reserva` INT(11) AFTER `id_reserva_producto`,
ADD CONSTRAINT `fk_entregas_stock_reservas` 
FOREIGN KEY (`id_reserva`) REFERENCES `reservas`(`id`);

ALTER TABLE `reservas`
ADD COLUMN `observaciones_picking` TEXT NULL AFTER `observaciones`;

ALTER TABLE `reservas`
ADD COLUMN `observaciones_packing` TEXT NULL AFTER `observaciones_picking`;