------------------------------------------------------------
--[2000003]--  ci_pais - filtro 
------------------------------------------------------------

------------------------------------------------------------
-- apex_objeto
------------------------------------------------------------

--- INICIO Grupo de desarrollo 2
INSERT INTO apex_objeto (proyecto, objeto, anterior, identificador, reflexivo, clase_proyecto, clase, punto_montaje, subclase, subclase_archivo, objeto_categoria_proyecto, objeto_categoria, nombre, titulo, colapsable, descripcion, fuente_datos_proyecto, fuente_datos, solicitud_registrar, solicitud_obj_obs_tipo, solicitud_obj_observacion, parametro_a, parametro_b, parametro_c, parametro_d, parametro_e, parametro_f, usuario, creacion, posicion_botonera) VALUES (
	'gafi', --proyecto
	'2000003', --objeto
	NULL, --anterior
	NULL, --identificador
	NULL, --reflexivo
	'toba', --clase_proyecto
	'toba_ei_filtro', --clase
	'101000001', --punto_montaje
	NULL, --subclase
	NULL, --subclase_archivo
	NULL, --objeto_categoria_proyecto
	NULL, --objeto_categoria
	'ci_pais - filtro', --nombre
	NULL, --titulo
	'0', --colapsable
	NULL, --descripcion
	'gafi', --fuente_datos_proyecto
	'gafi', --fuente_datos
	NULL, --solicitud_registrar
	NULL, --solicitud_obj_obs_tipo
	NULL, --solicitud_obj_observacion
	NULL, --parametro_a
	NULL, --parametro_b
	NULL, --parametro_c
	NULL, --parametro_d
	NULL, --parametro_e
	NULL, --parametro_f
	NULL, --usuario
	'2022-07-25 17:14:01', --creacion
	'abajo'  --posicion_botonera
);
--- FIN Grupo de desarrollo 2

------------------------------------------------------------
-- apex_objeto_ei_filtro
------------------------------------------------------------
INSERT INTO apex_objeto_ei_filtro (objeto_ei_filtro_proyecto, objeto_ei_filtro, ancho) VALUES (
	'gafi', --objeto_ei_filtro_proyecto
	'2000003', --objeto_ei_filtro
	'100%'  --ancho
);

------------------------------------------------------------
-- apex_objeto_ei_filtro_col
------------------------------------------------------------

--- INICIO Grupo de desarrollo 2
INSERT INTO apex_objeto_ei_filtro_col (objeto_ei_filtro_col, objeto_ei_filtro, objeto_ei_filtro_proyecto, tipo, nombre, expresion, etiqueta, descripcion, obligatorio, inicial, orden, estado_defecto, opciones_es_multiple, opciones_ef, carga_metodo, carga_clase, carga_include, carga_dt, carga_consulta_php, carga_sql, carga_fuente, carga_lista, carga_col_clave, carga_col_desc, carga_permite_no_seteado, carga_no_seteado, carga_no_seteado_ocultar, carga_maestros, edit_tamano, edit_maximo, edit_mascara, edit_unidad, edit_rango, edit_expreg, estilo, popup_item, popup_proyecto, popup_editable, popup_ventana, popup_carga_desc_metodo, popup_carga_desc_clase, popup_carga_desc_include, popup_puede_borrar_estado, punto_montaje, check_valor_si, check_valor_no, check_desc_si, check_desc_no, selec_cant_minima, selec_cant_maxima, selec_utilidades, selec_tamano, selec_ancho, selec_serializar, selec_cant_columnas, placeholder) VALUES (
	'2000001', --objeto_ei_filtro_col
	'2000003', --objeto_ei_filtro
	'gafi', --objeto_ei_filtro_proyecto
	'cadena', --tipo
	'nombre', --nombre
	'nombre', --expresion
	NULL, --etiqueta
	NULL, --descripcion
	'0', --obligatorio
	'0', --inicial
	'1', --orden
	NULL, --estado_defecto
	'0', --opciones_es_multiple
	NULL, --opciones_ef
	NULL, --carga_metodo
	NULL, --carga_clase
	NULL, --carga_include
	NULL, --carga_dt
	NULL, --carga_consulta_php
	NULL, --carga_sql
	NULL, --carga_fuente
	NULL, --carga_lista
	NULL, --carga_col_clave
	NULL, --carga_col_desc
	'0', --carga_permite_no_seteado
	NULL, --carga_no_seteado
	NULL, --carga_no_seteado_ocultar
	NULL, --carga_maestros
	NULL, --edit_tamano
	NULL, --edit_maximo
	NULL, --edit_mascara
	NULL, --edit_unidad
	NULL, --edit_rango
	NULL, --edit_expreg
	NULL, --estilo
	NULL, --popup_item
	NULL, --popup_proyecto
	NULL, --popup_editable
	NULL, --popup_ventana
	NULL, --popup_carga_desc_metodo
	NULL, --popup_carga_desc_clase
	NULL, --popup_carga_desc_include
	NULL, --popup_puede_borrar_estado
	NULL, --punto_montaje
	NULL, --check_valor_si
	NULL, --check_valor_no
	NULL, --check_desc_si
	NULL, --check_desc_no
	NULL, --selec_cant_minima
	NULL, --selec_cant_maxima
	NULL, --selec_utilidades
	NULL, --selec_tamano
	NULL, --selec_ancho
	NULL, --selec_serializar
	NULL, --selec_cant_columnas
	NULL  --placeholder
);
--- FIN Grupo de desarrollo 2
