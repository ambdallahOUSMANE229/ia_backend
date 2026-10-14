<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;

class ImportProductImages extends Command
{
    protected $signature = 'ouswear:import-images {product_id}';

    protected $description = 'Importe les images des produits et les convertit en WebP';

    /**
     * Charge une image selon son vrai format.
     */
    private function loadImage(string $file)
    {
        $info = @getimagesize($file);

        if (!$info || empty($info['mime'])) {
            return null;
        }

        return match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png'  => @imagecreatefrompng($file),
            'image/webp' => @imagecreatefromwebp($file),
            default      => null,
        };
    }

    public function handle(): int
    {
        $argument = $this->argument('product_id');

        if ($argument === 'all') {
            $products = Product::orderBy('id')->get();
        } else {
            $product = Product::find((int) $argument);

            if (!$product) {
                $this->error("Produit {$argument} introuvable.");

                return self::FAILURE;
            }

            $products = collect([$product]);
        }

        $sourcePath = storage_path('app/public/products');

        $totalImages = 0;

        foreach ($products as $product) {

            $this->newLine();

            $this->info(
                "Produit {$product->id} : {$product->name}"
            );

            /*
             * On recherche plusieurs extensions.
             *
             * Important :
             * certains fichiers .jpg sont en réalité des WebP.
             * getimagesize() déterminera leur vrai format.
             */
            $patterns = [
                $sourcePath . "/product {$product->id}-*.jpg",
                $sourcePath . "/product {$product->id}-*.jpeg",
                $sourcePath . "/product {$product->id}-*.png",
                $sourcePath . "/product {$product->id}-*.webp",
            ];

            $files = [];

            foreach ($patterns as $pattern) {
                $files = array_merge(
                    $files,
                    glob($pattern) ?: []
                );
            }

            /*
             * Évite les doublons.
             */
            $files = array_unique($files);

            /*
             * Tri naturel :
             * product 2-2.jpg
             * product 2-10.jpg
             * sera correctement classé.
             */
            sort($files, SORT_NATURAL);

            if (!$files) {
                $this->warn(
                    "Aucune image source trouvée pour le produit {$product->id}."
                );

                continue;
            }

            $this->info(
                count($files) . " image(s) trouvée(s)."
            );

            $firstImportedImage = null;

            foreach ($files as $file) {

                $filename = basename($file);

                /*
                 * Récupération du numéro de l'image.
                 */
                if (!preg_match(
                    '/product ' . $product->id . '-(\d+)\.(jpg|jpeg|png|webp)$/i',
                    $filename,
                    $matches
                )) {
                    continue;
                }

                $number = $matches[1];

                /*
                 * Détection du vrai format.
                 */
                $info = @getimagesize($file);

                if (!$info || empty($info['mime'])) {
                    $this->warn(
                        "Format impossible à détecter : {$filename}"
                    );

                    continue;
                }

                $realMime = $info['mime'];

                $image = $this->loadImage($file);

                if (!$image) {
                    $this->warn(
                        "Impossible de lire : {$filename} ({$realMime})"
                    );

                    continue;
                }

                $webpFilename =
                    "product-{$product->id}-{$number}.webp";

                $destination =
                    $sourcePath . '/' . $webpFilename;

                /*
                 * Conversion en WebP.
                 */
                if (!imagewebp(
                    $image,
                    $destination,
                    82
                )) {
                    $this->warn(
                        "Échec de conversion : {$filename}"
                    );

                    imagedestroy($image);

                    continue;
                }

                imagedestroy($image);

                /*
                 * On mémorise la première image importée.
                 */
                if ($firstImportedImage === null) {
                    $firstImportedImage =
                        'products/' . $webpFilename;
                }

                /*
                 * On ne modifie PAS automatiquement is_primary.
                 *
                 * Cela permet de conserver une image principale
                 * choisie manuellement.
                 */
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image' => 'products/' . $webpFilename,
                    ],
                    [
                        'sort_order' => (int) $number,
                    ]
                );

                $this->info(
                    "✓ {$filename} ({$realMime}) → {$webpFilename}"
                );

                $totalImages++;
            }

            /*
             * Si aucune image principale n'existe encore,
             * on utilise la première image importée.
             */
            if ($firstImportedImage !== null) {

                $hasPrimary = ProductImage::where(
                    'product_id',
                    $product->id
                )
                    ->where('is_primary', true)
                    ->exists();

                if (!$hasPrimary) {

                    ProductImage::where(
                        'product_id',
                        $product->id
                    )->update([
                        'is_primary' => false,
                    ]);

                    ProductImage::where(
                        'product_id',
                        $product->id
                    )
                        ->where(
                            'image',
                            $firstImportedImage
                        )
                        ->update([
                            'is_primary' => true,
                        ]);

                    $this->info(
                        "★ Image principale : {$firstImportedImage}"
                    );
                }
            }
        }

        $this->newLine();

        $this->info(
            "Import terminé : {$totalImages} image(s) convertie(s)."
        );

        return self::SUCCESS;
    }
}
