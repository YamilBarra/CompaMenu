#!/usr/bin/env bash
# Pruebas de seguridad de la sección 7.8.3 (Anexo H). Requisitos: APP_DEBUG=false, base con el seeder y
#   php artisan serve --port=8099      (en otra terminal). Uso: bash evidencias/pruebas_seguridad.sh
B=http://127.0.0.1:8099; J=$(mktemp -d)/cj
tok(){ curl -s -c $1 -b $1 $B$2 | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
say(){ printf '\n=== %s ===\n' "$*"; }
say "1 SQLi en el ingreso (correo con comillas)"; T=$(tok $J.a /ingresar)
curl -s -o /dev/null -w "HTTP %{http_code} -> %{redirect_url}\n" -c $J.a -b $J.a --data-urlencode "_token=$T" --data-urlencode "email=admin@compamenu.pe' OR '1'='1" --data-urlencode "password=' OR 1=1 --" $B/ingresar
say "2 SQLi con correo válido"; T=$(tok $J.a /ingresar)
curl -s -o /dev/null -w "HTTP %{http_code} -> %{redirect_url}\n" -c $J.a -b $J.a --data-urlencode "_token=$T" --data-urlencode "email=admin@compamenu.pe" --data-urlencode "password=' OR 1=1 --" $B/ingresar
say "7 CSRF: POST sin token"; curl -s -o /dev/null -w "HTTP %{http_code}\n" -X POST --data "email=a@b.pe&password=x" $B/ingresar
say "5 Campos vacíos en el registro"; T=$(tok $J.c /registro)
curl -s -o /dev/null -w "HTTP %{http_code} -> %{redirect_url}\n" -c $J.c -b $J.c --data-urlencode "_token=$T" --data "name=&email=&password=&password_confirmation=&rol=" $B/registro
say "3 XSS almacenado (reseña del estudiante de prueba)"; T=$(tok $J.d /ingresar)
curl -s -o /dev/null -c $J.d -b $J.d --data-urlencode "_token=$T" --data-urlencode "email=estudiante@compamenu.pe" --data-urlencode "password=Compa2026!" $B/ingresar
T=$(tok $J.d /resenas)
curl -s -o /dev/null -w "HTTP %{http_code} -> %{redirect_url}\n" -c $J.d -b $J.d --data-urlencode "_token=$T" --data "local_id=1&puntaje=5" --data-urlencode "comentario=<script>alert('XSS')</script>Muy rico y barato" $B/resenas
echo "Coincidencias de '<script>alert' en /resenas: $(curl -s $B/resenas | grep -c '<script>alert')"
say "4 XSS reflejado"; echo "Coincidencias sin escapar: $(curl -s -G --data-urlencode 'q=<script>alert(1)</script>' $B/ | grep -c '<script>alert(1)')"
say "8 Control de acceso: estudiante en /admin"; curl -s -o /dev/null -w "HTTP %{http_code}\n" -b $J.d $B/admin
say "11 Cabeceras"; curl -sI $B/ | grep -iE "^(HTTP|x-content|x-frame|referrer|permissions)"
say "12 Error seguro"; curl -s -o /dev/null -w "HTTP %{http_code}\n" $B/ruta-que-no-existe
say "9 Fuerza bruta (6 intentos)"; for i in 1 2 3 4 5 6; do T=$(tok $J.e /ingresar); curl -s -o /dev/null -w "intento $i: HTTP %{http_code}\n" -c $J.e -b $J.e --data-urlencode "_token=$T" --data "email=lucia@compamenu.pe&password=mala$i" $B/ingresar; done
curl -s -c $J.e -b $J.e $B/ingresar | grep -oiE "demasiados[^<]{0,80}" | head -1
