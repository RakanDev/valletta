<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StoreFlowTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $product = Product::create(['slug' => 'test-bag', 'name' => ['ar' => 'شنطة', 'en' => 'Bag'], 'price' => 15, 'is_active' => true]);
        $this->variant = $product->variants()->create(['name' => ['ar' => 'أسود', 'en' => 'Black'], 'stock' => 3]);
    }

    private function checkout(int $qty = 1): Order
    {
        $this->post('/cart/add', ['variant_id' => $this->variant->id, 'qty' => $qty])->assertRedirect('/cart');
        $this->post('/checkout', [
            'customer_name' => 'Sara', 'phone' => '0791234567', 'city' => 'amman', 'address' => 'Khalda street 5',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_pages_render_in_both_languages(): void
    {
        foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir) {
            $this->get("/lang/$locale");
            $this->get('/')->assertOk()->assertSee('dir="'.$dir.'"', false)->assertSee($locale === 'ar' ? 'شنطة' : 'Bag');
            $this->get('/p/test-bag')->assertOk()->assertSee($locale === 'ar' ? 'اطلبيها الآن' : 'Order now');
        }
    }

    public function test_translation_files_have_the_same_keys(): void
    {
        $ar = json_decode(file_get_contents(lang_path('ar.json')), true);
        $en = json_decode(file_get_contents(lang_path('en.json')), true);
        $this->assertSame([], array_diff_key($ar, $en));
        $this->assertSame([], array_diff_key($en, $ar));
    }

    public function test_checkout_creates_order_with_delivery_and_whatsapp_link(): void
    {
        $order = $this->checkout(2);

        $this->assertSame('VL-'.(1000 + $order->id), $order->number);
        $this->assertEquals(30, $order->subtotal);
        $this->assertEquals(2, $order->delivery_fee);
        $this->assertEquals(32, $order->total);
        $this->assertSame('new', $order->status);
        $this->assertSame(3, $this->variant->fresh()->stock, 'new orders do not touch stock');

        $this->get('/order/'.$order->number)->assertOk()->assertSee('https://wa.me/962797620010?text=', false);
        $this->assertSame([], session('cart', []));
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->post('/cart/add', ['variant_id' => $this->variant->id]);
        $this->post('/checkout', ['customer_name' => 'Sara', 'phone' => '12345', 'city' => 'amman', 'address' => 'Khalda street'])
            ->assertSessionHasErrors('phone');
        $this->assertSame(0, Order::count());
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $this->post('/cart/add', ['variant_id' => $this->variant->id, 'qty' => 5]);
        $this->post('/checkout', ['customer_name' => 'Sara', 'phone' => '0791234567', 'city' => 'amman', 'address' => 'Khalda street'])
            ->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::count());
    }

    public function test_sold_out_variant_cannot_be_added(): void
    {
        $this->variant->update(['stock' => 0]);
        $this->post('/cart/add', ['variant_id' => $this->variant->id])->assertSessionHasErrors('variant_id');
    }

    public function test_stock_moves_with_order_status(): void
    {
        $order = $this->checkout(2);

        $order->update(['status' => 'confirmed']);
        $this->assertSame(1, $this->variant->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_deducted);

        $order->update(['status' => 'shipped']);
        $order->update(['status' => 'delivered']);
        $this->assertSame(1, $this->variant->fresh()->stock, 'no double deduction');

        $order->update(['status' => 'returned']);
        $this->assertSame(3, $this->variant->fresh()->stock);
        $this->assertFalse($order->fresh()->stock_deducted);
    }

    public function test_confirming_without_enough_stock_fails(): void
    {
        $order = $this->checkout(2);
        $this->variant->update(['stock' => 1]);

        $this->expectException(ValidationException::class);
        $order->update(['status' => 'confirmed']);
    }

    public function test_deleting_confirmed_order_restores_stock(): void
    {
        $order = $this->checkout(1);
        $order->update(['status' => 'confirmed']);
        $order->delete();
        $this->assertSame(3, $this->variant->fresh()->stock);
    }

    public function test_order_page_is_private_to_its_browser(): void
    {
        $order = $this->checkout();
        $this->flushSession();
        $this->get('/order/'.$order->number)->assertNotFound();
    }
}
