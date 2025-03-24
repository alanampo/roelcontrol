CREATE TABLE `atributos` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_valores` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `valor` varchar(255) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `precio_extra` decimal(20,2) DEFAULT NULL,
  FOREIGN KEY (id_atributo) REFERENCES atributos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE atributos_valores_variedades (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  id_atributo_valor int(11) NOT NULL,
  id_variedad int(11) NOT NULL,
  FOREIGN KEY (id_atributo_valor) REFERENCES atributos_valores(id),
  FOREIGN KEY (id_variedad) REFERENCES variedades_producto(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--

CREATE TABLE `atributos_producto` (
  `id` int(11) NOT NULL,
  `id_tipo_producto` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `tipo_dato` varchar(20) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `id_tipo_atributo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `productos` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_interno` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `precio` decimal(11,2) DEFAULT NULL,
  `eliminado` int(1) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `imagen` mediumblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `atributos_producto_valores` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `valor_varchar` varchar(255) DEFAULT NULL,
  `valor_int` int(11) DEFAULT NULL,
  `valor_decimal` decimal(20,2) DEFAULT NULL,
  `valor_date` date DEFAULT NULL,
  `valor_text` text DEFAULT NULL,
  `valor_referencia_producto` int(11) DEFAULT NULL,
  `id_atributo_tipo_valor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_producto_valores_seleccionados` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_atributo_producto_valor` int(11) NOT NULL,
  `id_atributo_tipo_valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_servicio` (
  `id` int(11) NOT NULL,
  `id_tipo_servicio` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `tipo_dato` varchar(20) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `id_tipo_atributo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_servicio_valores` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `valor_varchar` varchar(255) DEFAULT NULL,
  `valor_int` int(11) DEFAULT NULL,
  `valor_decimal` decimal(20,2) DEFAULT NULL,
  `valor_date` date DEFAULT NULL,
  `valor_text` text DEFAULT NULL,
  `valor_referencia_servicio` int(11) DEFAULT NULL,
  `id_atributo_tipo_valor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_servicio_valores_seleccionados` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `id_atributo_servicio_valor` int(11) NOT NULL,
  `id_atributo_tipo_valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_tipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_tipos_valores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(40) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `precio_extra` decimal(20,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atributos_tipos_valores_precios` (
  `id` int(11) NOT NULL,
  `id_atributo_tipo_valor` int(11) NOT NULL,
  `id_vivero` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `atr_valores_precios_productos_viveros` (
  `id` int(11) NOT NULL,
  `id_vivero` int(11) NOT NULL,
  `id_atributo_tipo_valor` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `precio` decimal(20,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `pys_conceptos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `pys_viveros_precios` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_vivero` int(11) NOT NULL,
  `precio` decimal(20,2) NOT NULL,
  `precio_mayorista` decimal(20,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `id_interno` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `precio` decimal(11,2) DEFAULT NULL,
  `eliminado` int(1) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `imagen` mediumblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `tipos_servicio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `codigo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `viveros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `domicilio` varchar(120) DEFAULT NULL,
  `comuna` varchar(70) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rut` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for table `atributos_producto`
--
ALTER TABLE `atributos_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_producto` (`id_tipo_producto`),
  ADD KEY `id_tipo_atributo` (`id_tipo_atributo`);

--
-- Indexes for table `atributos_producto_valores`
--
ALTER TABLE `atributos_producto_valores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_atributo` (`id_atributo`),
  ADD KEY `valor_referencia_producto` (`valor_referencia_producto`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`);

--
-- Indexes for table `atributos_producto_valores_seleccionados`
--
ALTER TABLE `atributos_producto_valores_seleccionados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_atributo_producto_valor` (`id_atributo_producto_valor`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`);

--
-- Indexes for table `atributos_servicio`
--
ALTER TABLE `atributos_servicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_servicio` (`id_tipo_servicio`),
  ADD KEY `id_tipo_atributo` (`id_tipo_atributo`);

--
-- Indexes for table `atributos_servicio_valores`
--
ALTER TABLE `atributos_servicio_valores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_atributo` (`id_atributo`),
  ADD KEY `valor_referencia_servicio` (`valor_referencia_servicio`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`);

--
-- Indexes for table `atributos_servicio_valores_seleccionados`
--
ALTER TABLE `atributos_servicio_valores_seleccionados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_atributo_servicio_valor` (`id_atributo_servicio_valor`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`);

--
-- Indexes for table `atributos_tipos`
--
ALTER TABLE `atributos_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `atributos_tipos_valores`
--
ALTER TABLE `atributos_tipos_valores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indexes for table `atributos_tipos_valores_precios`
--
ALTER TABLE `atributos_tipos_valores_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`),
  ADD KEY `id_vivero` (`id_vivero`);

--
-- Indexes for table `atr_valores_precios_productos_viveros`
--
ALTER TABLE `atr_valores_precios_productos_viveros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_vivero` (`id_vivero`),
  ADD KEY `id_atributo_tipo_valor` (`id_atributo_tipo_valor`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_servicio` (`id_servicio`);


  ALTER TABLE `pys_conceptos`
  ADD PRIMARY KEY (`id`);


  ALTER TABLE `pys_viveros_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_vivero` (`id_vivero`);


  ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_tipo2` (`id_tipo`);


  ALTER TABLE `tipos_servicio`
  ADD PRIMARY KEY (`id`);

  ALTER TABLE `viveros`
  ADD PRIMARY KEY (`id`);


  ALTER TABLE `atributos_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_producto_valores`
--
ALTER TABLE `atributos_producto_valores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_producto_valores_seleccionados`
--
ALTER TABLE `atributos_producto_valores_seleccionados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_servicio`
--
ALTER TABLE `atributos_servicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_servicio_valores`
--
ALTER TABLE `atributos_servicio_valores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_servicio_valores_seleccionados`
--
ALTER TABLE `atributos_servicio_valores_seleccionados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_tipos`
--
ALTER TABLE `atributos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_tipos_valores`
--
ALTER TABLE `atributos_tipos_valores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atributos_tipos_valores_precios`
--
ALTER TABLE `atributos_tipos_valores_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atr_valores_precios_productos_viveros`
--
ALTER TABLE `atr_valores_precios_productos_viveros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--

--
-- AUTO_INCREMENT for table `pys_conceptos`
--
ALTER TABLE `pys_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


--
-- AUTO_INCREMENT for table `pys_viveros_precios`
--
ALTER TABLE `pys_viveros_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_servicio`
--
ALTER TABLE `tipos_servicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `viveros`
--
ALTER TABLE `viveros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `atributos_producto`
--
ALTER TABLE `atributos_producto`
  ADD CONSTRAINT `atributos_producto_ibfk_1` FOREIGN KEY (`id_tipo_producto`) REFERENCES `tipos_producto` (`id`),
  ADD CONSTRAINT `atributos_producto_ibfk_2` FOREIGN KEY (`id_tipo_atributo`) REFERENCES `atributos_tipos` (`id`);

--
-- Constraints for table `atributos_producto_valores`
--
ALTER TABLE `atributos_producto_valores`
  ADD CONSTRAINT `atributos_producto_valores_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `atributos_producto_valores_ibfk_2` FOREIGN KEY (`id_atributo`) REFERENCES `atributos_producto` (`id`),
  ADD CONSTRAINT `atributos_producto_valores_ibfk_3` FOREIGN KEY (`valor_referencia_producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `atributos_producto_valores_ibfk_4` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`);

--
-- Constraints for table `atributos_producto_valores_seleccionados`
--
ALTER TABLE `atributos_producto_valores_seleccionados`
  ADD CONSTRAINT `atributos_producto_valores_seleccionados_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `atributos_producto_valores_seleccionados_ibfk_2` FOREIGN KEY (`id_atributo_producto_valor`) REFERENCES `atributos_producto_valores` (`id`),
  ADD CONSTRAINT `atributos_producto_valores_seleccionados_ibfk_3` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`);

--
-- Constraints for table `atributos_servicio`
--
ALTER TABLE `atributos_servicio`
  ADD CONSTRAINT `atributos_servicio_ibfk_1` FOREIGN KEY (`id_tipo_servicio`) REFERENCES `tipos_servicio` (`id`),
  ADD CONSTRAINT `atributos_servicio_ibfk_2` FOREIGN KEY (`id_tipo_atributo`) REFERENCES `atributos_tipos` (`id`);

--
-- Constraints for table `atributos_servicio_valores`
--
ALTER TABLE `atributos_servicio_valores`
  ADD CONSTRAINT `atributos_servicio_valores_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`),
  ADD CONSTRAINT `atributos_servicio_valores_ibfk_2` FOREIGN KEY (`id_atributo`) REFERENCES `atributos_servicio` (`id`),
  ADD CONSTRAINT `atributos_servicio_valores_ibfk_3` FOREIGN KEY (`valor_referencia_servicio`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `atributos_servicio_valores_ibfk_4` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`);

--
-- Constraints for table `atributos_servicio_valores_seleccionados`
--
ALTER TABLE `atributos_servicio_valores_seleccionados`
  ADD CONSTRAINT `atributos_servicio_valores_seleccionados_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`),
  ADD CONSTRAINT `atributos_servicio_valores_seleccionados_ibfk_2` FOREIGN KEY (`id_atributo_servicio_valor`) REFERENCES `atributos_servicio_valores` (`id`),
  ADD CONSTRAINT `atributos_servicio_valores_seleccionados_ibfk_3` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`);

--
-- Constraints for table `atributos_tipos_valores`
--
ALTER TABLE `atributos_tipos_valores`
  ADD CONSTRAINT `atributos_tipos_valores_ibfk_12` FOREIGN KEY (`id_tipo`) REFERENCES `atributos_tipos` (`id`);

--
-- Constraints for table `atributos_tipos_valores_precios`
--
ALTER TABLE `atributos_tipos_valores_precios`
  ADD CONSTRAINT `atributos_tipos_valores_precios_ibfk_1` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`),
  ADD CONSTRAINT `atributos_tipos_valores_precios_ibfk_2` FOREIGN KEY (`id_vivero`) REFERENCES `viveros` (`id`);

--
-- Constraints for table `atr_valores_precios_productos_viveros`
--
ALTER TABLE `atr_valores_precios_productos_viveros`
  ADD CONSTRAINT `atr_valores_precios_productos_viveros_ibfk_1` FOREIGN KEY (`id_vivero`) REFERENCES `viveros` (`id`),
  ADD CONSTRAINT `atr_valores_precios_productos_viveros_ibfk_2` FOREIGN KEY (`id_atributo_tipo_valor`) REFERENCES `atributos_tipos_valores` (`id`),
  ADD CONSTRAINT `atr_valores_precios_productos_viveros_ibfk_3` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `atr_valores_precios_productos_viveros_ibfk_4` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`);


--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_id_tipo4` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_producto` (`id`);



--
-- Constraints for table `pys_viveros_precios`
--
ALTER TABLE `pys_viveros_precios`
  ADD CONSTRAINT `pys_viveros_precios_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `pys_viveros_precios_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`),
  ADD CONSTRAINT `pys_viveros_precios_ibfk_3` FOREIGN KEY (`id_vivero`) REFERENCES `viveros` (`id`);

--
-- Constraints for table `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_id_tipo5` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_servicio` (`id`);


