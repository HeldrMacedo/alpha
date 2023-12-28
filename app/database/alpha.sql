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