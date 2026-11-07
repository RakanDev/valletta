<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Support\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The first four Valletta models with their colors, stock (from the
 * supplier order sheet minus items already sold) and photos.
 * Safe to re-run: products are matched by slug and skipped if they exist.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([...$this->catalog(), ...$this->jsonCatalog()] as $i => $p) {
            if (Product::where('slug', $p['slug'])->exists()) {
                continue;
            }

            $product = Product::create([
                'slug' => $p['slug'],
                'sku' => $p['sku'],
                'name' => $p['name'],
                'short' => $p['short'],
                'description' => $p['description'],
                'features' => $p['features'],
                'dimensions' => $p['dimensions'],
                'price' => $p['price'],
                'is_active' => true,
                'sort' => $i + 1,
            ]);

            foreach ($p['variants'] as $vi => $v) {
                $variant = $product->variants()->create([
                    'name' => ['ar' => $v[0], 'en' => $v[1]],
                    'color_hex' => $v[2],
                    'stock' => $v[3],
                    'sort' => $vi,
                ]);
                foreach ($v[4] as $si => $file) {
                    $variant->images()->create(['product_id' => $product->id, 'path' => $this->upload($p['dir'], $file), 'sort' => $si]);
                }
            }

            foreach ($p['general'] as $si => $file) {
                $product->images()->create(['path' => $this->upload($p['dir'], $file), 'sort' => 100 + $si]);
            }
        }
    }

    private function upload(string $dir, string $file): string
    {
        $file = preg_replace('/\.webp$/', '', $file);
        $target = "products/{$dir}/{$file}.webp";
        Storage::disk(Media::disk())->put($target, file_get_contents(__DIR__."/images/{$dir}/{$file}.webp"), 'public');

        return $target;
    }

    /** Additional models, one JSON file each in database/seeders/catalog. */
    private function jsonCatalog(): array
    {
        $files = glob(__DIR__.'/catalog/*.json') ?: [];
        sort($files);

        return array_map(function (string $f) {
            $p = json_decode(file_get_contents($f), true, flags: JSON_THROW_ON_ERROR);
            $p['dimensions'] = ($p['dimensions'] ?? '') !== '' ? $p['dimensions'] : null;

            return $p;
        }, $files);
    }

    private function catalog(): array
    {
        return [
            [
                'slug' => 'valletta-cork-backpack',
                'sku' => 'VL-001',
                'dir' => 'cork',
                'price' => 16,
                'dimensions' => '29 × 28 × 15 cm',
                'name' => ['ar' => 'Valletta Cork Backpack', 'en' => 'Valletta Cork Backpack'],
                'short' => [
                    'ar' => 'تشكيلة الجامعة: شنطة ظهر صغيرة من قماش الفلين الطبيعي مع جلد نباتي بني. خفيفة، مقاومة للماء، وبنقشات مميزة.',
                    'en' => 'University Collection: a compact backpack in natural cork fabric with vegan brown leather trim. Lightweight, water-resistant, with distinctive patterns.',
                ],
                'description' => [
                    'ar' => "شنطة ظهر صغيرة مصنوعة من قماش الفلين الطبيعي (Cork Fabric) مع تفاصيل من الجلد النباتي البني. خامة طبيعية صديقة للبيئة، خفيفة ومقاومة للماء.\n\nمثالية لطالبة الجامعة، للكافيه، وللسفر الخفيف. خامة بتعيش سنين وما بتتوسخ بسهولة.",
                    'en' => "A compact backpack made from natural cork fabric with vegan brown leather trim. A natural, eco-friendly material that is lightweight and water-resistant.\n\nPerfect for university, café days and light travel. Durable and easy to keep clean.",
                ],
                'features' => [
                    'ar' => "خامة: فلين طبيعي + جلد نباتي بني\nجيب أمامي بسحاب + جيب خلفي بسحاب + جيب داخلي\nحمالات مريحة قابلة للتعديل\nبتوسع كتب، زجاجة مياه، وتابلت أو آيباد حتى 11 إنش\nنقشات مميزة، كل شنطة إلها شخصيتها",
                    'en' => "Natural cork + vegan brown leather\nFront zip pocket, back zip pocket and inner pocket\nComfortable adjustable straps\nFits books, a water bottle and a tablet or iPad up to 11\"\nDistinctive patterns, each bag with its own personality",
                ],
                'variants' => [
                    ['فسيفساء متوسطية', 'Mediterranean Mosaic', '#2F5D62', 11, ['mosaic', 'lifestyle-mosaic', 'lifestyle-mosaic-mall']],
                    ['ماندالا سداسية', 'Honeycomb Mandala', '#B5562E', 7, ['honeycomb', 'lifestyle-honeycomb']],
                    ['حديقة الفراشات', 'Butterfly Garden', '#A23B3B', 4, ['butterfly', 'lifestyle-butterfly']],
                    ['بيزلي كلاسيك', 'Baroque Paisley', '#3D3324', 4, ['paisley', 'lifestyle-paisley']],
                ],
                'general' => ['size', 'inside', 'back'],
            ],
            [
                'slug' => 'valletta-essential-backpack',
                'sku' => '01350',
                'dir' => 'essential',
                'price' => 18,
                'dimensions' => '35 × 34 × 16 cm',
                'name' => ['ar' => 'Valletta Essential Backpack', 'en' => 'Valletta Essential Backpack'],
                'short' => [
                    'ar' => 'شنطة ظهر نسائية للجامعة والكافيه، بتوسع لابتوب حتى 14 إنش، وبتتحول لشنطة كتف بحزامها الطويل.',
                    'en' => 'A women\'s backpack for campus and café days. Fits a laptop up to 14" and turns into a shoulder bag with its long strap.',
                ],
                'description' => [
                    'ar' => "شنطة ظهر نسائية مصممة لحياة الجامعة والكافيه في عمّان. بتجمع بين العملية والستايل العصري، خفيفة الوزن وأنيقة تناسب كل يوم.\n\nليش رح تحبيها؟ بتشيل اللابتوب بأمان، منظمة من جوا، خفيفة عالظهر، وبحزامها الطويل اللي بينفك بتتحول لشنطة كتف أو كروس. شكلها مرتب للجامعة والكافيه بعدها.",
                    'en' => "A women's backpack designed for university and café life in Amman. Practical, modern and lightweight for everyday use.\n\nWhy you'll love it: it carries your laptop safely, keeps you organized inside, sits light on your back, and the detachable long strap turns it into a shoulder or crossbody bag.",
                ],
                'features' => [
                    'ar' => "جيب مبطن للابتوب حتى 14 إنش\nحزام كتف طويل بينفك: ظهر، كتف، أو كروس\nجيب جانبي خارجي للموبايل\nجيب داخلي بسحاب للأشياء الثمينة + جيب تنظيم\nمقصورة رئيسية واسعة للكتب والدفاتر\nجلد صناعي PU عالي الجودة، مقاوم للخدش والماء الخفيف\nبطانة بوليستر متينة، وشراشيب جلدية أنيقة\nالوزن: 0.6 كغم",
                    'en' => "Padded laptop compartment, fits up to 14\"\nDetachable long strap: backpack, shoulder or crossbody\nEasy-access side phone pocket\nInner zip pocket for valuables + organizer slot\nSpacious main compartment for books and notebooks\nPremium PU leather, scratch- and splash-resistant\nDurable polyester lining and elegant leather tassel\nWeight: 0.6 kg",
                ],
                'variants' => [
                    ['بيج مع بني', 'Beige with brown', '#EDE3D2', 15, ['beige', 'lifestyle-beige']],
                    ['بني', 'Brown', '#5A3A24', 5, ['brown', 'lifestyle-brown']],
                    ['أسود', 'Black', '#1C1C1C', 4, ['black', 'lifestyle-black']],
                ],
                'general' => ['size', 'side', 'back', 'inside'],
            ],
            [
                'slug' => 'valletta-mini-backpack',
                'sku' => '9011',
                'dir' => 'mini',
                'price' => 25,
                'dimensions' => '30 × 25 × 13 cm',
                'name' => ['ar' => 'Valletta Mini Backpack', 'en' => 'Valletta Mini Backpack'],
                'short' => [
                    'ar' => 'شنطة ظهر ميني عملية من النايلون المقاوم للماء، بتفاصيل ذهبية وجيوب كثيرة.',
                    'en' => 'A compact everyday backpack in water-resistant nylon, with gold-tone details and plenty of pockets.',
                ],
                'description' => [
                    'ar' => "شنطة الظهر المثالية للطالبة العصرية، بتجمع بين الأناقة والعملية. خفيفة ومريحة مع مساحة كافية لكل احتياجاتك.\n\nمثالية للجامعة، الشغل، السفر، والطلعات اليومية، وبتناسب كل الفصول.",
                    'en' => "The perfect backpack for the modern student, combining elegance and practicality. Lightweight and comfortable with room for everything you need.\n\nPerfect for university, work, travel and everyday outings, in every season.",
                ],
                'features' => [
                    'ar' => "نايلون مقاوم للماء مع بطانة بوليستر\nسحابات ذهبية متينة\nجيب أمامي + مقصورة أمامية بجيوب تنظيم + مقصورة رئيسية واسعة\nجيب داخلي بسحاب + جيبين جانبيين\nجيب خلفي مخفي للأغراض المهمة\nظهر منحني مريح بيخفف الضغط\nبتوسع تابلت أو آيباد حتى 13 إنش وكتب ودفاتر (مش لابتوب)\nالعناية: لا تُغسل، امسحيها بقطعة قماش مبللة",
                    'en' => "Water-resistant nylon with polyester lining\nDurable gold-tone zippers\nFront pocket, front compartment with slip pockets, spacious main compartment\nInner zip pocket + two side pockets\nHidden back zip pocket for your valuables\nErgonomic curved back for all-day comfort\nFits a tablet or iPad up to 13\", books and notebooks (not a laptop)\nCare: do not wash, wipe with a damp cloth",
                ],
                'variants' => [
                    ['أسود كلاسيك', 'Classic Black', '#151515', 5, ['black', 'lifestyle-black']],
                    ['بيج', 'Beige', '#CFC6BA', 3, ['beige', 'lifestyle-beige']],
                    ['أخضر زمردي', 'Emerald Green', '#1F5A3C', 3, ['green', 'lifestyle-green']],
                ],
                'general' => ['views', 'size'],
            ],
            [
                'slug' => 'valletta-classic-backpack',
                'sku' => 'VL-004',
                'dir' => 'classic',
                'price' => 14,
                'dimensions' => '32 × 28 × 14 cm',
                'name' => ['ar' => 'Valletta Classic Backpack', 'en' => 'Valletta Classic Backpack'],
                'short' => [
                    'ar' => 'شنطة ظهر بتصميم كلاسيك من جلد PU مقاوم للماء، مثالية للجامعة والشغل، بـ 4 ألوان محايدة.',
                    'en' => 'A timeless classic backpack in water-resistant PU leather, perfect for university and work, in 4 neutral colors.',
                ],
                'description' => [
                    'ar' => "اكتشفي العملية والأناقة مع شنطة الظهر الكلاسيك من Valletta، مصممة للطالبة والمرأة العاملة في الأردن.\n\nليش رح تحبيها؟ لأنها خفيفة، بتشيل كتبك وأغراضك، وشكلها مرتب مع أي لبسة محجبة أو كاجوال. 4 ألوان محايدة بتناسب كل شي بخزانتك.",
                    'en' => "Discover practicality and elegance with the Valletta Classic Backpack, designed for students and working women in Jordan.\n\nWhy you'll love it: light, roomy and polished, it pairs with any modest or casual outfit. Four neutral colors that go with everything in your wardrobe.",
                ],
                'features' => [
                    'ar' => "جلد PU فاخر مقاوم للماء\nبطانة بوليستر ناعمة\nسوستة قوية وآمنة\nجيب رئيسي واسع + جيب أمامي بسوستة + جيبين جانبيين\nجيب داخلي مخفي بسوستة للأشياء الثمينة\nظهر منحني مريح للكتف\nبتوسع آيباد أو تابلت حتى 11 إنش + كتب ودفاتر\nالعناية: لا تُغسل بالماء، امسحيها بقطعة قماش مبللة",
                    'en' => "Premium water-resistant PU leather\nSoft polyester lining\nStrong, secure zippers\nSpacious main compartment, front zip pocket, two side pockets\nHidden inner zip pocket for your valuables\nErgonomic curved back for shoulder comfort\nFits a tablet or iPad up to 11\", books and notebooks\nCare: do not wash, wipe with a damp cloth",
                ],
                'variants' => [
                    ['كاميل', 'Camel', '#A8642E', 9, ['camel']],
                    ['أسود', 'Black', '#161616', 7, ['black', 'lifestyle-black']],
                    ['بيج', 'Beige', '#E3DBCF', 4, ['beige', 'lifestyle-beige']],
                    ['رمادي', 'Grey', '#6E6E70', 2, ['grey', 'lifestyle-grey']],
                ],
                'general' => ['size', 'inside'],
            ],
        ];
    }
}
