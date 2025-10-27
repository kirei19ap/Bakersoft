SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `bakersoft` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bakersoft`;

DROP TABLE IF EXISTS `categoriamp`;
CREATE TABLE IF NOT EXISTS `categoriamp` (
  `idCatMP` int NOT NULL AUTO_INCREMENT,
  `nombreCatMP` varchar(50) NOT NULL,
  PRIMARY KEY (`idCatMP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `detallepedido`;
CREATE TABLE IF NOT EXISTS `detallepedido` (
  `idDetallePedido` int NOT NULL AUTO_INCREMENT,
  `idPedido` int NOT NULL,
  `idMP` int NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`idDetallePedido`),
  KEY `fk_detallepedido_materiaprima` (`idMP`),
  KEY `fk_detallepedido_pedido` (`idPedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `detalle_licencia`;
CREATE TABLE IF NOT EXISTS `detalle_licencia` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_licencia` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `cantidad_dias` int NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `idx_detalle_licencia` (`id_licencia`),
  KEY `idx_detalle_rango` (`fecha_inicio`,`fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE IF NOT EXISTS `empleados` (
  `id_empleado` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `sexo` enum('Masculino','Femenino','Otro','Prefiero no decir') DEFAULT NULL,
  `id_estado_civil` int DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `dni` varchar(15) NOT NULL,
  `cuil` varchar(11) DEFAULT NULL,
  `legajo` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `provincia` int NOT NULL,
  `localidad` int NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `id_puesto` int DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_baja` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `dni` (`dni`),
  UNIQUE KEY `uq_empleado_usuario` (`usuario_id`),
  UNIQUE KEY `uq_empleados_cuil` (`cuil`),
  UNIQUE KEY `uq_empleados_legajo` (`legajo`),
  UNIQUE KEY `uq_empleados_usuario` (`usuario_id`),
  KEY `idx_emp_nombre_apellido` (`apellido`,`nombre`),
  KEY `idx_empleados_prov` (`provincia`),
  KEY `idx_empleados_loc` (`localidad`),
  KEY `idx_empleados_eliminado` (`eliminado`),
  KEY `idx_empleados_fecha_baja` (`fecha_baja`),
  KEY `idx_empleados_puesto` (`id_puesto`),
  KEY `idx_empleados_id_estado_civil` (`id_estado_civil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `estadospedidos`;
CREATE TABLE IF NOT EXISTS `estadospedidos` (
  `codEstado` int NOT NULL,
  `descEstado` varchar(50) NOT NULL,
  PRIMARY KEY (`codEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `estados_licencia`;
CREATE TABLE IF NOT EXISTS `estados_licencia` (
  `id_estado` int NOT NULL AUTO_INCREMENT,
  `nombre` enum('Nueva','Pendiente de envío','Pendiente de aprobación','Cancelada','Aprobada','Rechazada') NOT NULL,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `uq_estados_licencia_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `estado_civil`;
CREATE TABLE IF NOT EXISTS `estado_civil` (
  `id_estado_civil` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_estado_civil`),
  UNIQUE KEY `descripcion` (`descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `licencia`;
CREATE TABLE IF NOT EXISTS `licencia` (
  `id_licencia` int NOT NULL AUTO_INCREMENT,
  `id_empleado` int NOT NULL,
  `id_tipo` int NOT NULL,
  `id_estado` int NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `observaciones` varchar(200) DEFAULT NULL,
  `motivo_rechazo` varchar(200) DEFAULT NULL,
  `usuario_creacion` int DEFAULT NULL,
  `usuario_resolucion` int DEFAULT NULL,
  PRIMARY KEY (`id_licencia`),
  KEY `idx_licencia_emp_estado` (`id_empleado`,`id_estado`),
  KEY `idx_licencia_tipo` (`id_tipo`),
  KEY `fk_licencia_estado` (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `licencia_cambios`;
CREATE TABLE IF NOT EXISTS `licencia_cambios` (
  `id_cambio` int NOT NULL AUTO_INCREMENT,
  `id_licencia` int NOT NULL,
  `id_empleado` int NOT NULL,
  `campo` varchar(40) NOT NULL,
  `valor_anterior` varchar(255) DEFAULT NULL,
  `valor_nuevo` varchar(255) DEFAULT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cambio`),
  KEY `id_licencia` (`id_licencia`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `localidades`;
CREATE TABLE IF NOT EXISTS `localidades` (
  `id_localidad` int NOT NULL AUTO_INCREMENT,
  `id_provincia` int NOT NULL,
  `localidad` varchar(255) NOT NULL,
  PRIMARY KEY (`id_localidad`),
  KEY `fk_provincia_localidad` (`id_provincia`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `materiaprima`;
CREATE TABLE IF NOT EXISTS `materiaprima` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `lote` varchar(30) NOT NULL,
  `unidad_medida` varchar(10) NOT NULL,
  `es_perecedero` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_vencimiento` date DEFAULT NULL,
  `stockminimo` int NOT NULL,
  `stockactual` int NOT NULL,
  `proveedor` int DEFAULT NULL,
  `estado` varchar(10) NOT NULL,
  `categoriaMP` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mp_nombre_lote` (`nombre`,`lote`),
  KEY `fk_materia_proveedor` (`proveedor`),
  KEY `fk_categoriaMP` (`categoriaMP`),
  KEY `idx_mp_perecedero_vto` (`es_perecedero`,`fecha_vencimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `pedidomp`;
CREATE TABLE IF NOT EXISTS `pedidomp` (
  `idPedido` int NOT NULL AUTO_INCREMENT,
  `idProveedor` int NOT NULL,
  `fechaPedido` date NOT NULL,
  `Estado` int DEFAULT NULL,
  PRIMARY KEY (`idPedido`),
  KEY `fk_pedidomp_proveedor` (`idProveedor`),
  KEY `fk_estado_pedido` (`Estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE IF NOT EXISTS `proveedor` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `calle` varchar(100) NOT NULL,
  `altura` int NOT NULL,
  `provincia` int NOT NULL,
  `localidad` int NOT NULL,
  `estado` varchar(20) NOT NULL,
  PRIMARY KEY (`id_proveedor`),
  KEY `fk_proveedor_provincia` (`provincia`),
  KEY `fk_proveedor_localidad` (`localidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `provincias`;
CREATE TABLE IF NOT EXISTS `provincias` (
  `id_provincia` int NOT NULL AUTO_INCREMENT,
  `provincia` varchar(255) NOT NULL,
  PRIMARY KEY (`id_provincia`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `puesto`;
CREATE TABLE IF NOT EXISTS `puesto` (
  `idPuesto` int NOT NULL AUTO_INCREMENT,
  `descrPuesto` varchar(50) NOT NULL,
  PRIMARY KEY (`idPuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `tipos_licencia`;
CREATE TABLE IF NOT EXISTS `tipos_licencia` (
  `id_tipo` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(80) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `impacta_banco_vacaciones` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_tipo`),
  UNIQUE KEY `uq_tipos_licencia_desc` (`descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nomyapellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_baja` datetime DEFAULT NULL,
  `rol` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `fk_rol_usuarios` (`rol`),
  KEY `idx_usuarios_fecha_creacion` (`fecha_creacion`),
  KEY `idx_usuarios_fecha_baja` (`fecha_baja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `vacaciones_config`;
CREATE TABLE IF NOT EXISTS `vacaciones_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `antiguedad_desde` int NOT NULL,
  `antiguedad_hasta` int DEFAULT NULL,
  `dias` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `detallepedido`
  ADD CONSTRAINT `fk_detallepedido_materiaprima` FOREIGN KEY (`idMP`) REFERENCES `materiaprima` (`id`),
  ADD CONSTRAINT `fk_detallepedido_pedido` FOREIGN KEY (`idPedido`) REFERENCES `pedidomp` (`idPedido`);

ALTER TABLE `detalle_licencia`
  ADD CONSTRAINT `fk_detalle_licencia` FOREIGN KEY (`id_licencia`) REFERENCES `licencia` (`id_licencia`) ON DELETE CASCADE;

ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_empleado_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_empleados_estado_civil` FOREIGN KEY (`id_estado_civil`) REFERENCES `estado_civil` (`id_estado_civil`),
  ADD CONSTRAINT `fk_empleados_id_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puesto` (`idPuesto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_empleados_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `licencia`
  ADD CONSTRAINT `fk_licencia_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_licencia_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_licencia` (`id_estado`),
  ADD CONSTRAINT `fk_licencia_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_licencia` (`id_tipo`);

ALTER TABLE `localidades`
  ADD CONSTRAINT `fk_provincia_localidad` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`);

ALTER TABLE `materiaprima`
  ADD CONSTRAINT `fk_categoriaMP` FOREIGN KEY (`categoriaMP`) REFERENCES `categoriamp` (`idCatMP`),
  ADD CONSTRAINT `fk_materia_proveedor` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`id_proveedor`);

ALTER TABLE `pedidomp`
  ADD CONSTRAINT `fk_estado_pedido` FOREIGN KEY (`Estado`) REFERENCES `estadospedidos` (`codEstado`),
  ADD CONSTRAINT `fk_pedidomp_proveedor` FOREIGN KEY (`idProveedor`) REFERENCES `proveedor` (`id_proveedor`);

ALTER TABLE `proveedor`
  ADD CONSTRAINT `fk_proveedor_localidad` FOREIGN KEY (`localidad`) REFERENCES `localidades` (`id_localidad`),
  ADD CONSTRAINT `fk_proveedor_provincia` FOREIGN KEY (`provincia`) REFERENCES `provincias` (`id_provincia`);

ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_rol_usuarios` FOREIGN KEY (`rol`) REFERENCES `roles` (`id_rol`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
