<?php
// Uso: php evidencias/medir.php <ruta-del-proyecto> <etiqueta>   (mide 7 páginas: mediana de 15 ejecuciones, consultas y tiempo en la base)
[$_, $path, $label] = $argv;
require $path.'/vendor/autoload.php';
$app = require $path.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Cache; use Illuminate\Http\Request;

$admin = App\Models\User::where('rol','administrador')->first();
$escenarios = [
  'S1 Inicio: precio<=10, distancia<=500 m' => ['/?precio=10&distancia=500', false, false],
  'S2 Inicio: buscador por nombre "lomo"'   => ['/?precio=12&q=lomo', false, false],
  'S3 Inicio: solo menús con remate'        => ['/?precio=15&rem=1', false, false],
  'S4 Locales (catálogo)'                   => ['/locales', false, false],
  'S5 Reseñas'                              => ['/resenas', false, false],
  'S6 Admin: resumen (caché vacío)'         => ['/admin', true, true],
  'S7 Admin: resumen (caché lleno)'         => ['/admin', true, false],
];
$res = [];
foreach ($escenarios as $nombre => [$url, $esAdmin, $flush]) {
    $tiempos = []; $nq = 0; $dbms = 0; $queries = [];
    for ($i = 0; $i < 17; $i++) {
        if ($flush) { Cache::flush(); }
        $q = []; DB::flushQueryLog(); DB::enableQueryLog();
        $req = Request::create($url, 'GET');
        $app->instance('request', $req); if ($esAdmin) { app('auth')->guard('web')->setUser($admin); }
        $t0 = hrtime(true);
        $resp = $kernel->handle($req);
        $ms = (hrtime(true) - $t0) / 1e6;
        $log = DB::getQueryLog();
        if ($i >= 2) { $tiempos[] = $ms; }
        if ($i === 2) {
            $nq = count($log); $dbms = array_sum(array_column($log, 'time'));
            foreach ($log as $l) { $queries[] = ['sql'=>$l['query'], 'bindings'=>$l['bindings'], 'ms'=>$l['time']]; }
            $status = $resp->getStatusCode(); $bytes = strlen($resp->getContent());
        }
        $kernel->terminate($req, $resp);
    }
    sort($tiempos);
    $res[$nombre] = ['status'=>$status,'bytes'=>$bytes,'mediana_ms'=>round($tiempos[intdiv(count($tiempos),2)],1),
        'min_ms'=>round($tiempos[0],1),'consultas'=>$nq,'db_ms'=>round($dbms,1),'queries'=>$queries];
    echo str_pad($nombre,45).' http='.$status.' mediana='.$res[$nombre]['mediana_ms']." ms  consultas=$nq db=".round($dbms,1)." ms\n";
}
file_put_contents(__DIR__."/medicion_$label.json", json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
