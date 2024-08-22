
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	'Autoridades', --nombre
	NULL, --nivel_acceso
	'Autoridades de la facultad', --descripcion
	NULL, --vencimiento
	NULL, --dias
	NULL, --hora_entrada
	NULL, --hora_salida
	NULL, --listar
	'1', --permite_edicion
	NULL  --menu_usuario
);

------------------------------------------------------------
-- apex_usuario_grupo_acc_item
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
--- FIN Grupo de desarrollo 0

--- INICIO Grupo de desarrollo 2
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2000020'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2000021'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2000022'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2000023'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'2000024'  --item
);
--- FIN Grupo de desarrollo 2

--- INICIO Grupo de desarrollo 103
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'103000009'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'103000010'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'103000011'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'103000012'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'gafi', --proyecto
	'autoridades', --usuario_grupo_acc
	NULL, --item_id
	'103000013'  --item
);
--- FIN Grupo de desarrollo 103
