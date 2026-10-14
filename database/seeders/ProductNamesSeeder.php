<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductNamesSeeder extends Seeder
{
    public function run(): void
    {
        $products = [

            // Pantalons
            1 => [
                'name' => 'Essential Cargo Pants',
                'description' => 'Pantalon cargo masculin au style moderne, pensé pour un look urbain et confortable.',
            ],

            // T-shirts
            2 => [
                'name' => 'Essential Oversized T-Shirt',
                'description' => 'T-shirt oversize masculin à la coupe contemporaine, idéal pour un style streetwear.',
            ],

            // Chaussures
            3 => [
                'name' => 'Urban Runner White',
                'description' => 'Sneakers masculines au design épuré, conçues pour accompagner les looks urbains.',
            ],

            // T-shirts
            4 => [
                'name' => 'Premium Cotton T-Shirt',
                'description' => 'T-shirt masculin en coton premium avec une silhouette élégante et minimaliste.',
            ],

            // Accessoires
            5 => [
                'name' => 'Signature Bracelet',
                'description' => 'Bracelet masculin minimaliste conçu pour compléter une tenue avec une touche raffinée.',
            ],

            // Pantalons
            6 => [
                'name' => 'Relaxed Fit Trousers',
                'description' => 'Pantalon masculin à la coupe relaxed, parfait pour une silhouette moderne.',
            ],

            // Vestes
            7 => [
                'name' => 'Urban Bomber Jacket',
                'description' => 'Bomber masculin contemporain inspiré de la mode urbaine.',
            ],

            // T-shirts
            8 => [
                'name' => 'Classic Oversized Tee',
                'description' => 'T-shirt oversize masculin au design minimaliste et polyvalent.',
            ],

            // Montres
            9 => [
                'name' => 'Minimal Steel Watch',
                'description' => 'Montre masculine au cadran minimaliste et au bracelet métallique élégant.',
            ],

            // Pantalons
            10 => [
                'name' => 'Modern Straight Pants',
                'description' => 'Pantalon masculin à la coupe droite pour un style élégant et contemporain.',
            ],

            // Sacs
            11 => [
                'name' => 'Urban Crossbody Bag',
                'description' => 'Sac bandoulière masculin compact et fonctionnel pour un usage quotidien.',
            ],

            // Pantalons
            12 => [
                'name' => 'Street Cargo Pants',
                'description' => 'Cargo masculin inspiré du streetwear avec une coupe confortable.',
            ],

            // Vestes
            13 => [
                'name' => 'Essential Overshirt',
                'description' => 'Surchemise masculine structurée, idéale pour les superpositions.',
            ],

            // Pantalons
            14 => [
                'name' => 'Premium Relaxed Pants',
                'description' => 'Pantalon masculin premium à la coupe décontractée et élégante.',
            ],

            // Chaussures
            15 => [
                'name' => 'OUSWEAR Street Runner',
                'description' => 'Sneakers masculines inspirées du streetwear contemporain.',
            ],

            // T-shirts
            16 => [
                'name' => 'Essential Logo Tee',
                'description' => 'T-shirt masculin essentiel avec une identité OUSWEAR minimaliste.',
            ],

            // Accessoires
            17 => [
                'name' => 'OUSWEAR Signature Chain',
                'description' => 'Chaîne masculine au design sobre pour compléter une tenue contemporaine.',
            ],

            // Pantalons
            18 => [
                'name' => 'Urban Utility Pants',
                'description' => 'Pantalon utilitaire masculin pensé pour un style urbain fonctionnel.',
            ],

            // T-shirts
            19 => [
                'name' => 'Heavyweight Cotton Tee',
                'description' => 'T-shirt masculin en coton épais avec une coupe premium.',
            ],

            // Pantalons
            20 => [
                'name' => 'Classic Tailored Pants',
                'description' => 'Pantalon masculin à la coupe élégante pour les silhouettes modernes.',
            ],

            // Chaussures
            21 => [
                'name' => 'Cloud Street Sneakers',
                'description' => 'Sneakers masculines confortables au design contemporain.',
            ],

            // Pantalons
            22 => [
                'name' => 'Daily Essential Pants',
                'description' => 'Pantalon masculin polyvalent pensé pour les looks du quotidien.',
            ],

            // Sacs
            23 => [
                'name' => 'Premium Shoulder Bag',
                'description' => 'Sac épaule masculin au design épuré et pratique.',
            ],

            // Chaussures
            24 => [
                'name' => 'Classic Low Sneakers',
                'description' => 'Sneakers basses masculines au style intemporel.',
            ],

            // Chaussures
            25 => [
                'name' => 'Urban Essential Sneakers',
                'description' => 'Sneakers masculines modernes adaptées aux silhouettes urbaines.',
            ],

            // T-shirts
            26 => [
                'name' => 'Minimal Signature Tee',
                'description' => 'T-shirt masculin minimaliste avec une identité premium.',
            ],

            // Pantalons
            27 => [
                'name' => 'Wide Fit Trousers',
                'description' => 'Pantalon masculin wide fit inspiré des tendances contemporaines.',
            ],

            // Accessoires
            28 => [
                'name' => 'Signature Leather Belt',
                'description' => 'Ceinture masculine au design sobre et élégant.',
            ],

            // Montres
            29 => [
                'name' => 'OUSWEAR Classic Watch',
                'description' => 'Montre masculine élégante pensée pour accompagner les looks du quotidien.',
            ],

            // Pantalons
            30 => [
                'name' => 'Urban Straight Pants',
                'description' => 'Pantalon masculin à la coupe droite et au style urbain.',
            ],

            // Vestes
            31 => [
                'name' => 'Premium Street Jacket',
                'description' => 'Veste masculine premium inspirée de l’esthétique streetwear.',
            ],

            // T-shirts
            32 => [
                'name' => 'Daily Oversized Tee',
                'description' => 'T-shirt oversize masculin confortable pour les looks quotidiens.',
            ],

            // Chaussures
            33 => [
                'name' => 'Premium Court Sneakers',
                'description' => 'Sneakers masculines premium au design épuré et polyvalent.',
            ],

            // Casquettes
            34 => [
                'name' => 'Classic OUSWEAR Cap',
                'description' => 'Casquette masculine OUSWEAR au design minimaliste.',
            ],

            // Casquettes
            35 => [
                'name' => 'Essential Street Cap',
                'description' => 'Casquette masculine inspirée du streetwear contemporain.',
            ],

            // Pantalons
            36 => [
                'name' => 'Urban Comfort Pants',
                'description' => 'Pantalon masculin confortable conçu pour les silhouettes urbaines.',
            ],

            // Sacs
            37 => [
                'name' => 'Daily Crossbody Bag',
                'description' => 'Sac bandoulière masculin pratique pour accompagner les déplacements quotidiens.',
            ],

            // Casquettes
            38 => [
                'name' => 'OUSWEAR Signature Cap',
                'description' => 'Casquette masculine signature avec une identité OUSWEAR affirmée.',
            ],

            // Pantalons
            39 => [
                'name' => 'Modern Cargo Pants',
                'description' => 'Cargo masculin moderne combinant confort et esthétique urbaine.',
            ],

            // Vestes
            40 => [
                'name' => 'Essential Denim Jacket',
                'description' => 'Veste denim masculine intemporelle revisitée dans un esprit OUSWEAR.',
            ],
        ];

        foreach ($products as $id => $data) {
            Product::where('id', $id)->update([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);
        }

        $this->command->info('Les 40 produits OUSWEAR ont été mis à jour.');
    }
}

