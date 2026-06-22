<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Importa empleados desde database/data/empleados.csv (export del Google Sheet
 * de firmas de COMPULAGO) y los asigna a su sucursal segun la direccion/ciudad.
 *
 * Columnas (por indice): 0 NOMBRE, 1 CARGO, 2 CORREO, 3 DIRECCION, 4 CIUDAD,
 * 5 FOTO, 6 LOCATION, 7 TELEFONO, 8 TIPO DE FIRMA, 9 Formula.
 */
class EmployeeImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/empleados.csv');
        if (! is_file($path)) {
            $this->command->error("No se encontro el CSV: {$path}");
            return;
        }

        $branches = Branch::all();

        $imported = 0;
        $skipped  = [];
        $unmatched = [];

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // descartar encabezado

        while (($row = fgetcsv($handle)) !== false) {
            $name      = trim($row[0] ?? '');
            $position  = trim($row[1] ?? '');
            $address   = trim($row[3] ?? '');
            $city      = trim($row[4] ?? '');
            $phone     = trim($row[7] ?? '');
            $signature = trim($row[8] ?? '');

            if ($name === '') {
                continue;
            }

            // Descartar filas internas / a eliminar / sin ubicacion
            $cargoUpper = mb_strtoupper($position);
            if (
                $cargoUpper === 'USO INTERNO' ||
                $cargoUpper === 'ELIMINAR' ||
                Str::contains(mb_strtolower($name), 'uso interno') ||
                ($address === '' && $city === '')
            ) {
                $skipped[] = "{$name} ({$position})";
                continue;
            }

            // Solo ejecutivos de ventas y asesores comerciales
            if (! preg_match('/ejecutiv|asesor/i', $position)) {
                $skipped[] = "{$name} ({$position})";
                continue;
            }

            $branch = $this->resolveBranch($branches, $address, $city, $position, $signature);
            if (! $branch) {
                $unmatched[] = "{$name} | {$city} | {$address}";
                continue;
            }

            $employee = Employee::firstOrNew([
                'name'      => $name,
                'branch_id' => $branch->id,
            ]);

            $employee->position  = $position ?: 'Asesor Comercial';
            $employee->card_type = $this->cardType($signature, $position);
            $employee->whatsapp  = $this->mobile($phone);

            if (! $employee->exists || blank($employee->slug)) {
                $employee->slug = Employee::generateSlug($name);
            }

            $employee->save();
            $imported++;
        }

        fclose($handle);

        $this->command->info("Empleados importados/actualizados: {$imported}");
        $this->command->warn('Omitidos (internos/eliminar): ' . count($skipped));
        foreach ($skipped as $s) {
            $this->command->line("  - {$s}");
        }
        if ($unmatched) {
            $this->command->error('SIN SEDE (revisar manualmente): ' . count($unmatched));
            foreach ($unmatched as $u) {
                $this->command->line("  ! {$u}");
            }
        }
    }

    /**
     * Resuelve la sucursal por palabras clave de la direccion, con respaldo por ciudad.
     */
    private function resolveBranch($branches, string $address, string $city, string $position, string $signature): ?Branch
    {
        $a = mb_strtolower(Str::ascii($address));
        $cityName = mb_strtolower(Str::ascii(trim(explode('-', $city)[0])));
        $corporate = Str::contains(mb_strtolower($signature), 'corporativa')
            || Str::contains(mb_strtolower($position), 'corporativo');

        $find = fn (string $needle) => $branches->first(
            fn ($b) => Str::contains(mb_strtolower(Str::ascii($b->name)), $needle)
        );

        // Reglas por centro comercial / local (mas especificas primero)
        if (Str::contains($a, 'castellana'))                       return $find('castellana');
        if (Str::contains($a, 'centro uno'))                       return $find('centro uno');
        if (Str::contains($a, 'suchiimma'))                        return $find('suchiimma');
        if (Str::contains($a, 'alamedas'))                         return $find('alamedas');
        if (Str::contains($a, 'ocean'))                            return $find('ocean');
        if (Str::contains($a, 'plazuela 23'))                      return $find('plazuela 23');
        if (Str::contains($a, 'paseo centra'))                     return $find('paseo central');
        if (Str::contains($a, 'plazuela') && $cityName === 'cartagena') return $find('plazuela in');
        if (Str::contains($a, 'ternera'))                          return $find('electrolago cartagena');

        // Edificio principal de Cartagena (Pedro Heredia): principal o corporativo
        if (Str::contains($a, 'pedro heredia') || Str::contains($a, 'edif. compulago')) {
            return $corporate ? $find('corporativo') : $find('sede principal cartagena');
        }

        // Respaldo por ciudad
        return match ($cityName) {
            'barranquilla' => $find('electrolago barranquilla'),
            'pereira'      => $find('electrolago pereira'),
            'valledupar'   => $find('sede principal valledupar'),
            'riohacha'     => $find('electrolago riohacha'),
            'monteria'     => $find('paseo central'),
            'santa marta'  => $find('electrolago santa marta'),
            'cartagena'    => $find('sede principal cartagena'),
            default        => null,
        };
    }

    private function cardType(string $signature, string $position): string
    {
        $s = mb_strtolower($signature . ' ' . $position);
        if (Str::contains($s, 'corporativ')) return Employee::CARD_TYPE_CORPORATE;
        if (Str::contains($s, 'brilla') || Str::contains($s, 'credito')) return Employee::CARD_TYPE_CREDIT;
        return Employee::CARD_TYPE_NORMAL;
    }

    /**
     * Extrae el numero celular (10 digitos que empiezan por 3) y lo deja en
     * formato internacional 57XXXXXXXXXX para los enlaces wa.me.
     */
    private function mobile(string $phone): ?string
    {
        if ($phone === '') {
            return null;
        }

        foreach (preg_split('/[\/]/', $phone) as $part) {
            $digits = preg_replace('/\D/', '', $part);
            if (strlen($digits) === 10 && $digits[0] === '3') {
                return '57' . $digits;
            }
        }

        return null;
    }
}
