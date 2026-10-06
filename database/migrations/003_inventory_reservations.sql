CREATE TABLE IF NOT EXISTS inventario_reservas (
    inventario_id INT UNSIGNED NOT NULL PRIMARY KEY,
    token CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    expira_em DATETIME NOT NULL,
    INDEX idx_reservas_token (token),
    INDEX idx_reservas_expiracao (expira_em),
    CONSTRAINT fk_reservas_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id)
) ENGINE=InnoDB;
