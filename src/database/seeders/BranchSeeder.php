<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Sucursales de COMPULAGO tomadas de https://compulago.com/nuestras-tiendas/
     */
    public function run(): void
    {
        $branches = [
            // Cartagena
            [
                'name'     => 'Sede Principal Cartagena',
                'city'     => 'Cartagena',
                'address'  => 'Av Pedro de Heredia CL30C No 60G-45 Br Chipre',
                'maps_url' => 'https://goo.gl/maps/2XroFhpMkZStae1w9',
                'phone'    => '3043441197 / 3154636875',
            ],
            [
                'name'     => 'Sede Paseo de la Castellana',
                'city'     => 'Cartagena',
                'address'  => 'Centro Comercial La Castellana Local 133-136',
                'maps_url' => 'https://goo.gl/maps/f3CkqYYAoTPwsmMw8',
                'phone'    => '3165214146',
            ],
            [
                'name'     => 'Sede Centro Uno TOO',
                'city'     => 'Cartagena',
                'address'  => 'CC Centro Uno Local 104 La Matuna',
                'maps_url' => 'https://goo.gl/maps/WTWSufpGrivwtuS38',
                'phone'    => '3188163283',
            ],
            [
                'name'     => 'Sede La Plazuela In',
                'city'     => 'Cartagena',
                'address'  => 'Cl. 31 #71 130, Centro Comercial la Plazuela - Local 108',
                'maps_url' => 'https://goo.gl/maps/JVYc1kiFuVpG53L19',
                'phone'    => '3187419014',
            ],
            [
                'name'     => 'Electrolago Cartagena',
                'city'     => 'Cartagena',
                'address'  => 'Ternera diagonal 31 #82C-29 lote A2',
                'maps_url' => 'https://maps.app.goo.gl/6pjNmzETus8kkqLb8',
                'phone'    => '3235301930 / 3146040751',
            ],
            [
                'name'     => 'Dpto. Corporativo',
                'city'     => 'Cartagena',
                'address'  => 'Edificio Sede Principal, Piso 4',
                'maps_url' => null,
                'phone'    => '3218401121 / 3183519977',
            ],

            // Barranquilla
            [
                'name'     => 'Electrolago Barranquilla',
                'city'     => 'Barranquilla',
                'address'  => 'CRA 44 #40-21 Centro',
                'maps_url' => 'https://maps.app.goo.gl/kckigioZxMy2Bmbm7',
                'phone'    => '3114162618 / 3213347044',
            ],

            // Montería
            [
                'name'     => 'Paseo Central',
                'city'     => 'Montería',
                'address'  => 'CC Paseo Central Local 5-6 Cl 32 No 4-06 Centro',
                'maps_url' => 'https://goo.gl/maps/zHQyPuwdLrS45m7YA',
                'phone'    => '3174407652 / 3217058308',
            ],
            [
                'name'     => 'Alamedas',
                'city'     => 'Montería',
                'address'  => 'CC Alamedas Local B120 - Av Circunvalar Cl 44 No 8-43',
                'maps_url' => 'https://goo.gl/maps/MvTQhf4G8XR3bFT89',
                'phone'    => '3182853852',
            ],

            // Riohacha
            [
                'name'     => 'Electrolago Riohacha',
                'city'     => 'Riohacha',
                'address'  => 'Cl 15 No 8-35',
                'maps_url' => 'https://goo.gl/maps/aw4VE87Pybij7Dh66',
                'phone'    => '3147335069 / 3147900882',
            ],
            [
                'name'     => 'Suchiimma',
                'city'     => 'Riohacha',
                'address'  => 'CC Suchiimma Local 46 - Cl 15 No 8-56',
                'maps_url' => 'https://goo.gl/maps/Y87sHpe5efv3a3sE6',
                'phone'    => '3157532955 / 3168875334',
            ],

            // Santa Marta
            [
                'name'     => 'Ocean Mall',
                'city'     => 'Santa Marta',
                'address'  => 'CC Ocean Mall Local 22 - Av del Ferrocarril Cl 29 No 15-100',
                'maps_url' => 'https://goo.gl/maps/Z83Rdw8AmAXTk8iEA',
                'phone'    => '3163879448 / 3145908483',
            ],
            [
                'name'     => 'Plazuela 23',
                'city'     => 'Santa Marta',
                'address'  => 'CC Plazuela 23 Local 29 - Cl 23 No 6-18',
                'maps_url' => null,
                'phone'    => '3156525548 / 3234501305',
            ],
            [
                'name'     => 'Electrolago Santa Marta',
                'city'     => 'Santa Marta',
                'address'  => 'Calle 22 #6-35, Centro',
                'maps_url' => 'https://maps.app.goo.gl/dV1akwvbJ58xWFyZA',
                'phone'    => '3104002413',
            ],

            // Valledupar
            [
                'name'     => 'Sede Principal Valledupar',
                'city'     => 'Valledupar',
                'address'  => 'KR 11 No 16A - 52 Br Loperena',
                'maps_url' => 'https://goo.gl/maps/pvqMZqsenZ9AUQnH9',
                'phone'    => '3188276516',
            ],

            // Pereira
            [
                'name'     => 'Electrolago Pereira',
                'city'     => 'Pereira',
                'address'  => 'Calle 25 # 8-10 Centro',
                'maps_url' => null,
                'phone'    => '3225076771',
            ],
        ];

        foreach ($branches as $branch) {
            $row = Branch::updateOrCreate(
                ['name' => $branch['name'], 'city' => $branch['city']],
                $branch
            );

            if (blank($row->slug)) {
                $row->update(['slug' => Branch::generateSlug($row->name)]);
            }
        }
    }
}
