-- Carga de volumen de prueba para medir consultas (sección 7.7 y Anexo G).
-- Uso:  php artisan migrate:fresh --seed   y luego   mariadb -u root compamenu < evidencias/carga_volumen.sql
-- Requiere MariaDB (motor SEQUENCE: seq_1_to_N). Crea ≈25 mil usuarios, 5 mil locales, 40 mil menús,
-- 120 mil platos, 8 mil remates, 150 mil valoraciones y 100 mil reservas (datos sintéticos).
SET autocommit=0; SET foreign_key_checks=0; SET unique_checks=0;
SET @pw='$2y$12$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ0123456';
DELETE FROM reservas; DELETE FROM valoraciones;
INSERT INTO users(name,email,password,rol,telefono,activo,created_at,updated_at)
SELECT CONCAT('Dueño ',seq), CONCAT('dueno',seq,'@bulk.pe'), @pw,'establecimiento','984000000',1,NOW(),NOW() FROM seq_1_to_5000;
INSERT INTO users(name,email,password,rol,telefono,activo,created_at,updated_at)
SELECT CONCAT('Estudiante ',seq), CONCAT('est',seq,'@bulk.pe'), @pw,'estudiante','984000000',1,NOW(),NOW() FROM seq_1_to_20000;
COMMIT;
-- Los AUTO_INCREMENT pueden tener saltos: se numeran las filas con tablas auxiliares en lugar de suponer IDs contiguos
DROP TABLE IF EXISTS tmp_own, tmp_est, tmp_loc, tmp_menu;
CREATE TABLE tmp_own AS SELECT ROW_NUMBER() OVER (ORDER BY id) n, id FROM users WHERE email LIKE 'dueno%@bulk.pe';
CREATE TABLE tmp_est AS SELECT ROW_NUMBER() OVER (ORDER BY id) n, id FROM users WHERE email LIKE 'est%@bulk.pe';
ALTER TABLE tmp_own ADD PRIMARY KEY(n); ALTER TABLE tmp_est ADD PRIMARY KEY(n);
INSERT INTO locales(user_id,nombre,tipo_local,direccion,distancia_campus,horario,telefono,descripcion,activo,created_at,updated_at)
SELECT o.id, CONCAT('Local ',o.n), ELT(1+(o.n MOD 3),'pension','kiosko','comedor'), CONCAT('Calle ',o.n), 20+(o.n*37 MOD 3000),'Lun a Vie 12:00-15:00','984000000','Local de prueba',IF(o.n MOD 10=0,0,1),NOW(),NOW() FROM tmp_own o;
CREATE TABLE tmp_loc AS SELECT ROW_NUMBER() OVER (ORDER BY id) n, id FROM locales WHERE nombre LIKE 'Local %';
ALTER TABLE tmp_loc ADD PRIMARY KEY(n);
INSERT INTO menus(local_id,nombre,descripcion,precio,tipo,acompanamiento,bebida,fecha,porciones,disponible,foto,created_at,updated_at)
SELECT l.id, CONCAT('Menu ',s.seq,' ',ELT(1+(s.seq MOD 5),'lomo','aji','chairo','ceviche','menestron')), 'Menu de prueba', 3+(s.seq MOD 1700)/100, ELT(1+(s.seq MOD 4),'criollo','andino','vegetariano','proteico'),'Arroz','Chicha',CURDATE(),10,IF(s.seq MOD 5=0,0,1),NULL,NOW(),NOW()
FROM seq_1_to_40000 s JOIN tmp_loc l ON l.n = 1+((s.seq*7) MOD 5000);
CREATE TABLE tmp_menu AS SELECT ROW_NUMBER() OVER (ORDER BY id) n, id FROM menus WHERE nombre LIKE 'Menu %';
ALTER TABLE tmp_menu ADD PRIMARY KEY(n);
INSERT INTO platos(menu_id,nombre,categoria,descripcion,orden,created_at,updated_at)
SELECT m.id, CONCAT('Plato ',s.seq), ELT(1+((s.seq-1) MOD 3),'entrada','segundo','postre'),NULL,(s.seq-1) MOD 3,NOW(),NOW() FROM seq_1_to_120000 s JOIN tmp_menu m ON m.n=1+((s.seq-1) DIV 3);
INSERT INTO remates(local_id,menu_id,precio_remate,porciones,hora_inicio,hora_fin,activo,created_at,updated_at)
SELECT mm.local_id,mm.id,GREATEST(1,mm.precio-2),5,'16:00:00','18:00:00',IF(t.n MOD 3=0,0,1),NOW(),NOW() FROM tmp_menu t JOIN menus mm ON mm.id=t.id WHERE t.n MOD 5=1;
INSERT INTO valoraciones(local_id,user_id,puntaje,comentario,calidad,porcion,precio,higiene,atencion,visible,created_at,updated_at)
SELECT l.id, e.id, 1+(s.seq MOD 5),'Buen menu',4,4,4,4,4,IF(s.seq MOD 12=0,0,1), NOW()-INTERVAL (s.seq MOD 120) DAY, NOW()
FROM seq_1_to_150000 s JOIN tmp_loc l ON l.n=1+((s.seq-1) MOD 5000) JOIN tmp_est e ON e.n=1+((s.seq-1) DIV 5000);
INSERT INTO reservas(codigo,user_id,local_id,menu_id,remate_id,cantidad,precio_unitario,fecha_recojo,hora_recojo,nota,estado,created_at,updated_at)
SELECT CONCAT('B',LPAD(s.seq,7,'0')), e.id, mm.local_id, mm.id, NULL, 1, mm.precio, CURDATE(),'13:00:00',NULL, ELT(1+(s.seq MOD 5),'pendiente','confirmada','completada','cancelada','rechazada'), NOW()-INTERVAL (s.seq MOD 120) DAY, NOW()
FROM seq_1_to_100000 s JOIN tmp_menu m ON m.n=1+(s.seq MOD 40000) JOIN menus mm ON mm.id=m.id JOIN tmp_est e ON e.n=1+(s.seq MOD 20000);
COMMIT;
DROP TABLE tmp_own, tmp_est, tmp_loc, tmp_menu;
SET foreign_key_checks=1; SET unique_checks=1;
ANALYZE TABLE users,locales,menus,platos,remates,valoraciones,reservas;
SELECT 'users' t,COUNT(*) n FROM users UNION ALL SELECT 'locales',COUNT(*) FROM locales UNION ALL SELECT 'menus',COUNT(*) FROM menus UNION ALL SELECT 'platos',COUNT(*) FROM platos UNION ALL SELECT 'remates',COUNT(*) FROM remates UNION ALL SELECT 'valoraciones',COUNT(*) FROM valoraciones UNION ALL SELECT 'reservas',COUNT(*) FROM reservas;
