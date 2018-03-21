CREATE TABLE `pavalco`.`bodexistencia` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idparte` INT NOT NULL,
  `idbodega` INT NOT NULL,
  `cantidad` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`));
