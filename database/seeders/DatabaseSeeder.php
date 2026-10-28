<?php

namespace Database\Seeders;

use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;
use Illuminate\Database\Seeder;

/**
 * Datos de ejemplo de CompaMenu (los mismos locales y menús del prototipo, ahora en MySQL).
 * Todas las cuentas de ejemplo usan la contraseña: Compa2026!
 */
class DatabaseSeeder extends Seeder
{
    private const CLAVE = 'Compa2026!';

    public function run(): void
    {
        // ---------- Administrador ----------
        User::create([
            'name' => 'Administrador CompaMenu',
            'email' => 'admin@compamenu.pe',
            'password' => self::CLAVE,
            'rol' => User::ROL_ADMIN,
        ]);

        // ---------- Establecimientos (usuario + local) ----------
        $locales = [];
        $datosLocales = [
            'inti' => ['Comedor Inti', 'comedor', 'Av. de la Cultura 421', 300, 'Lun a Sáb 11:30 – 15:30', '984 100 201', 'Comedor familiar con menú criollo y andino todos los días.'],
            'rosa' => ['Pensión Doña Rosa', 'pension', 'Calle Tullumayo 118', 150, 'Lun a Vie 12:00 – 15:00', '984 100 202', 'Comida casera cusqueña, como en casa.'],
            'fit' => ['Fit Campus', 'comedor', 'Av. Collasuyo 250', 100, 'Lun a Sáb 11:00 – 16:00', '984 100 203', 'Platos altos en proteína para estudiantes que entrenan.'],
            'juana' => ['Kiosko Mama Juana', 'kiosko', 'Puerta 2 del campus', 80, 'Lun a Vie 10:00 – 16:00', '984 100 204', 'Sándwiches, ensaladas y bebidas rápidas.'],
            'andes' => ['Comedor Los Andes', 'comedor', 'Jr. Garcilaso 330', 450, 'Lun a Sáb 12:00 – 16:00', '984 100 205', 'Sabores del Cusco: chairo, estofado y más.'],
            'verde' => ['Verde Sabor', 'comedor', 'Calle Triunfo 87', 220, 'Lun a Vie 11:30 – 15:00', '984 100 206', 'Cocina vegetariana y de temporada.'],
        ];
        foreach ($datosLocales as $clave => [$nombre, $tipo, $dir, $dist, $horario, $tel, $desc]) {
            $dueno = User::create([
                'name' => 'Dueño de '.$nombre,
                'email' => $clave.'@compamenu.pe',
                'password' => self::CLAVE,
                'rol' => User::ROL_ESTABLECIMIENTO,
                'telefono' => $tel,
            ]);
            $locales[$clave] = Local::create([
                'user_id' => $dueno->id, 'nombre' => $nombre, 'tipo_local' => $tipo, 'direccion' => $dir,
                'distancia_campus' => $dist, 'horario' => $horario, 'telefono' => $tel, 'descripcion' => $desc,
            ]);
        }

        // ---------- Menús con sus platos (los mismos del prototipo) ----------
        $menus = [];
        $datosMenus = [
            'aji' => ['inti', 'Ají de gallina con arroz', 7.00, 'criollo', 'img/aji-de-gallina.jpg', 'Arroz blanco', 'Chicha morada', 20,
                [['entrada', 'Sopa de verduras'], ['segundo', 'Ají de gallina con arroz'], ['postre', 'Gelatina']]],
            'lomo' => ['rosa', 'Lomo saltado con papas fritas', 7.50, 'criollo', 'img/lomo-saltado.jpg', 'Arroz y papas fritas', 'Emoliente', 18,
                [['entrada', 'Ensalada fresca'], ['segundo', 'Lomo saltado con papas fritas']]],
            'ceviche' => ['fit', 'Ceviche de trucha con camote y choclo', 9.00, 'proteico', 'img/ceviche.jpg', 'Camote y choclo', 'Refresco de maracuyá', 8,
                [['segundo', 'Ceviche de trucha con camote y choclo']]],
            'menestron' => ['juana', 'Menestrón y guiso de lentejas con huevo', 5.50, 'vegetariano', 'img/menestron.jpg', 'Arroz blanco', 'Jugo de naranja', 12,
                [['entrada', 'Menestrón'], ['segundo', 'Guiso de lentejas con huevo']]],
            'chairo' => ['andes', 'Chairo cusqueño y estofado de res', 8.00, 'andino', 'img/chairo.jpg', 'Chuño y arroz', 'Mate de coca', 14,
                [['entrada', 'Chairo cusqueño'], ['segundo', 'Estofado de res']]],
            'zapallo' => ['verde', 'Crema de zapallo y tortilla de verduras', 7.50, 'vegetariano', null, 'Ensalada criolla', 'Refresco de piña', 10,
                [['entrada', 'Crema de zapallo'], ['segundo', 'Tortilla de verduras']]],
            'caldo' => ['inti', 'Caldo de gallina y arroz con pollo', 6.00, 'criollo', null, 'Arroz con pollo', 'Chicha morada', 15,
                [['entrada', 'Caldo de gallina'], ['segundo', 'Arroz con pollo']]],
        ];
        foreach ($datosMenus as $clave => [$local, $nombre, $precio, $tipo, $foto, $acomp, $bebida, $porciones, $platos]) {
            $menu = Menu::create([
                'local_id' => $locales[$local]->id, 'nombre' => $nombre, 'precio' => $precio, 'tipo' => $tipo,
                'foto' => $foto, 'acompanamiento' => $acomp, 'bebida' => $bebida, 'porciones' => $porciones,
                'fecha' => now()->toDateString(), 'hora_inicio' => '11:30', 'hora_fin' => '15:30', 'disponible' => true,
                'descripcion' => 'Menú del día de '.$locales[$local]->nombre.'.',
            ]);
            foreach ($platos as $i => [$categoria, $plato]) {
                $menu->platos()->create(['nombre' => $plato, 'categoria' => $categoria, 'orden' => $i]);
            }
            $menus[$clave] = $menu;
        }

        // ---------- Remates solidarios (menú, precio de remate, porciones, desde, hasta) ----------
        foreach ([['aji', 4.50, 9, '15:00', '17:00'], ['lomo', 5.00, 4, '16:00', '18:00'], ['chairo', 5.00, 6, '15:30', '17:30']] as [$m, $precio, $porciones, $desde, $hasta]) {
            Remate::create([
                'local_id' => $menus[$m]->local_id, 'menu_id' => $menus[$m]->id,
                'precio_remate' => $precio, 'porciones' => $porciones, 'hora_inicio' => $desde, 'hora_fin' => $hasta,
            ]);
        }

        // ---------- Estudiantes y valoraciones ----------
        $estudiantes = [];
        foreach ([
            'estudiante' => 'Estudiante de Prueba', 'lucia' => 'Lucía M.', 'marco' => 'Marco T.', 'andrea' => 'Andrea Q.',
        ] as $clave => $nombre) {
            $estudiantes[$clave] = User::create([
                'name' => $nombre, 'email' => $clave.'@compamenu.pe', 'password' => self::CLAVE, 'rol' => User::ROL_ESTUDIANTE,
            ]);
        }
        foreach ([
            ['lucia', 'rosa', 5, 'Porciones generosas y el local siempre está limpio.', ['calidad' => 5, 'porcion' => 5, 'higiene' => 5]],
            ['marco', 'inti', 4, 'Buen sabor y atienden rápido entre clases.', ['calidad' => 4, 'atencion' => 4]],
            ['andrea', 'juana', 3, 'Es barato, pero la porción de arroz es pequeña.', ['precio' => 4, 'porcion' => 2]],
        ] as [$est, $local, $puntaje, $comentario, $aspectos]) {
            Valoracion::create([
                'local_id' => $locales[$local]->id, 'user_id' => $estudiantes[$est]->id,
                'puntaje' => $puntaje, 'comentario' => $comentario,
            ] + $aspectos);
        }

        // ---------- Una reserva de ejemplo (ya descuenta las porciones) ----------
        $menus['lomo']->decrement('porciones', 2);
        Reserva::create([
            'codigo' => 'DEMO01', 'user_id' => $estudiantes['estudiante']->id,
            'local_id' => $menus['lomo']->local_id, 'menu_id' => $menus['lomo']->id,
            'cantidad' => 2, 'precio_unitario' => 7.50, 'fecha_recojo' => now()->toDateString(),
            'hora_recojo' => '13:00', 'nota' => 'Sin ají, por favor', 'estado' => 'pendiente',
        ]);
    }
}
