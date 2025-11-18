
USE c2880645_hubtec;

DROP TABLE IF EXISTS c2880645_ttime.maintenances;
DROP TABLE IF EXISTS c2880645_ttime.programations;
DROP TABLE IF EXISTS c2880645_ttime.clients;
DROP TABLE IF EXISTS c2880645_ttime.vehicles;
DROP TABLE IF EXISTS c2880645_ttime.providers;
DROP TABLE IF EXISTS c2880645_ttime.drivers;
DROP TABLE IF EXISTS c2880645_ttime.logging;

DROP TABLE IF EXISTS c2880645_hubtec.permissions;
DROP TABLE IF EXISTS c2880645_hubtec.options;
DROP TABLE IF EXISTS c2880645_hubtec.users;
DROP TABLE IF EXISTS c2880645_hubtec.companies;
DROP TABLE IF EXISTS c2880645_hubtec.documents;
DROP TABLE IF EXISTS c2880645_hubtec.ubigeos;


CREATE TABLE IF NOT EXISTS c2880645_hubtec.ubigeos (
	code_department VARCHAR(2) NOT NULL,
	name_department VARCHAR(50) NOT NULL,
	code_province VARCHAR(2) NOT NULL,
	name_province VARCHAR(50) NOT NULL,
	code_district VARCHAR(2) NOT NULL,
	name_district VARCHAR(50) NOT NULL,
	order_ubigeo INT NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS c2880645_hubtec.documents (
	id INT NOT NULL,
	name_short VARCHAR(30) NOT NULL,
	name_long VARCHAR(50) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	state INT NOT NULL DEFAULT(1),
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS c2880645_hubtec.companies (
	id INT NOT NULL,
	full_name VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL,
	phone_number VARCHAR(10) NOT NULL,
	document_id INT NOT NULL,
	document_number VARCHAR(15) NOT NULL,
	code_department VARCHAR(2) NOT NULL,
	code_province VARCHAR(2) NOT NULL,
	code_district VARCHAR(2) NOT NULL,
	path VARCHAR(100) NOT NULL,
	address VARCHAR(100) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	state INT NOT NULL DEFAULT (1),
    PRIMARY KEY (id),
	FOREIGN KEY(document_id) REFERENCES c2880645_hubtec.documents(id)
);


/*
state
	0 = inactivo/eliminado
	1 = activo

profile
	0 = admin
	1 = supervisor
	2 = default
*/
CREATE TABLE IF NOT EXISTS c2880645_hubtec.users (
	id INT NOT NULL,
	company_id INT NOT NULL,
	full_name VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL,
	phone_number VARCHAR(10) NOT NULL,
	password VARCHAR(100) NOT NULL,
	tries INT NOT NULL,
	profile INT NOT NULL,
	document_id INT NOT NULL,
	document_number VARCHAR(15) NOT NULL,
	code_department VARCHAR(2) DEFAULT NULL,
	code_province VARCHAR(2) DEFAULT NULL,
	code_district VARCHAR(2) DEFAULT NULL,
	address VARCHAR(100),
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	last_login DATETIME,
	state INT NOT NULL DEFAULT(1),
    PRIMARY KEY (id),
	FOREIGN KEY(company_id) REFERENCES c2880645_hubtec.companies(id),
	FOREIGN KEY(document_id) REFERENCES c2880645_hubtec.documents(id)
);


CREATE TABLE IF NOT EXISTS c2880645_hubtec.options (
	id INT NOT NULL,
	sub_id INT NOT NULL,
	company_id INT NOT NULL,
	main_view INT NOT NULL,
	full_name VARCHAR(100) NOT NULL,
	url VARCHAR(100) NOT NULL,
	icon VARCHAR(100) NOT NULL,
	order_option INT NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	state INT NOT NULL DEFAULT(1),
    PRIMARY KEY (id),
	FOREIGN KEY(company_id) REFERENCES c2880645_hubtec.companies(id)
);


CREATE TABLE IF NOT EXISTS c2880645_hubtec.permissions (
	id INT NOT NULL,
	option_id INT NOT NULL,
	user_id INT NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	state INT NOT NULL DEFAULT(1),
    PRIMARY KEY (id),
	FOREIGN KEY(option_id) REFERENCES c2880645_hubtec.options(id),
	FOREIGN KEY(user_id) REFERENCES c2880645_hubtec.users(id)
);

