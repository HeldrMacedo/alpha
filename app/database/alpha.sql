CREATE TABLE regiao(
	id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
	nome VARCHAR(200) NOT NULL,
	unit_id INT NOT NULL,
	CONSTRAINT fk_regiao_unit_id FOREIGN KEY (unit_id) REFERENCES system_unit (id)
);

CREATE TABLE gerentes(
	id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
	nome VARCHAR(200) NOT NULL,
	regiao_id INT NOT NULL,
	user_id INT NOT NULL,
	unit_id INT NOT NULL,
	CONSTRAINT FK_userIDRegiao FOREIGN KEY (user_id) REFERENCES system_user(id),
   CONSTRAINT FK_RegiaoIdUser FOREIGN KEY (regiao_id) REFERENCES regiao(id),
   CONSTRAINT FK_unitGerente  FOREIGN KEY (unit_id) REFERENCES system_unit(id)
);

CREATE TABLE cambistas(
	id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
	regiao_id INT NOT NULL,
	gerente_id INT NOT NULL,
	nome VARCHAR(200) NOT NULL,
	comissao DECIMAL(5,2) NOT NULL,
	pode_cancelar CHAR(1) NOT NULL,
	pode_cancelar_tempo TIME,
	limite_venda DECIMAL(15,2),
	exibe_comissao CHAR(1) NOT NULL,
	usuario_id INT NOT NULL,
	pode_reimprimir CHAR(1) NOT NULL,
	unit_id INT NOT NULL,
	CONSTRAINT FK_comissao_regiao_id FOREIGN KEY (regiao_id) REFERENCES regiao(id),
	CONSTRAINT FK_comissao_gerente_id FOREIGN KEY (gerente_id) REFERENCES gerentes(id),
	CONSTRAINT FK_comissao_usuario_if FOREIGN KEY (usuario_id) REFERENCES system_user(id),
	CONSTRAINT FK_comissao_unit_id FOREIGN KEY (unit_id) REFERENCES system_unit(id)
	
);