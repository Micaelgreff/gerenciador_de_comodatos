
CREATE TABLE IF NOT EXISTS `users` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`password` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`active` TINYINT(4) NULL DEFAULT '1',
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`updated_at` DATETIME NULL DEFAULT current_timestamp(),
	`last_access_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE
);


CREATE TABLE IF NOT EXISTS `comodantes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome_fantasia` VARCHAR(255) NOT NULL,
    `razao_social` VARCHAR(255) NOT NULL,
    `cnpj` VARCHAR(14) NOT NULL,
    `endereco` TEXT NOT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `comodatarios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome_completo` VARCHAR(255) NOT NULL,
    `cpf` VARCHAR(11) NOT NULL,
    `endereco` TEXT NOT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `tipos_equipamentos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `descricao` TEXT,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `marcas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `identificador_externo` VARCHAR(100),
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `modelos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `tipo_equipamento_id` INT UNSIGNED NOT NULL,
    `marca_id` INT UNSIGNED NOT NULL,
    `descricao` TEXT,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	CONSTRAINT `fk_modelos_tipo`
     FOREIGN KEY (tipo_equipamento_id)
     REFERENCES tipos_equipamentos(id),
     
    CONSTRAINT `fk_modelos_marca`
        FOREIGN KEY (marca_id)
        REFERENCES marcas(id),
        
   CONSTRAINT `uq_modelo_marca_tipo`
        UNIQUE (marca_id, tipo_equipamento_id, nome)
);

CREATE TABLE IF NOT EXISTS `inventario` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `modelo_id` INT UNSIGNED NOT NULL,
    `patrimonio` VARCHAR(100) NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_inventario_modelo`
        FOREIGN KEY (modelo_id)
        REFERENCES modelos(id)
);

CREATE TABLE IF NOT EXISTS `comodatos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `comodante_id` INT UNSIGNED NOT NULL,
    `comodatario_id` INT UNSIGNED NOT NULL,
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE,
    `observacao` TEXT,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_comodatos_comodante`
        FOREIGN KEY (comodante_id)
        REFERENCES comodantes(id),

    CONSTRAINT `fk_comodatos_comodatario`
        FOREIGN KEY (comodatario_id)
        REFERENCES comodatarios(id)
);


CREATE TABLE IF NOT EXISTS `comodato_itens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `comodato_id` INT UNSIGNED NOT NULL,
    `inventario_id` INT UNSIGNED NOT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_comodato_itens_comodato`
        FOREIGN KEY (comodato_id)
        REFERENCES comodatos(id),

    CONSTRAINT `fk_comodato_itens_inventario`
        FOREIGN KEY (inventario_id)
        REFERENCES inventario(id),

    CONSTRAINT `uq_comodato_inventario`
        UNIQUE (comodato_id, inventario_id)
);


