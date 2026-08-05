<?php

namespace Database\Seeders;

use App\Actions\SaveOrderItem;
use App\Actions\SubmitOrder;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Enums\ItemType;
use App\Enums\OrderStatus;
use App\Enums\RightLeft;
use App\Enums\ZipperSide;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * POUZE PRO LOKÁLNÍ VÝVOJ — založí demo účty a ukázkovou zakázku.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Účty: dilna / demo-heslo-dilna  (admin), lucie / demo-heslo-lucie (designérka)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->error('DemoSeeder je určen jen pro lokální prostředí.');

            return;
        }

        $this->call(DatabaseSeeder::class);

        $admin = User::updateOrCreate(
            ['username' => 'dilna'],
            ['name' => 'Šicí dílna', 'password' => 'demo-heslo-dilna', 'is_admin' => true, 'is_active' => true],
        );

        $lucie = User::updateOrCreate(
            ['username' => 'lucie'],
            [
                'name' => 'Lucie Návrhářka',
                'password' => 'demo-heslo-lucie',
                'phone' => '+420 777 123 456',
                'address' => "Interiérové studio Lucie\nKvětná 12\n602 00 Brno",
                'is_admin' => false,
                'is_active' => true,
            ],
        );

        if ($lucie->orders()->exists()) {
            return;
        }

        $saveItem = app(SaveOrderItem::class);

        // Odeslaná zakázka se dvěma položkami
        $submittedOrder = Order::create(['user_id' => $lucie->id, 'name' => 'Novákovi – obývák', 'status' => OrderStatus::Draft]);

        $saveItem->handle(
            order: $submittedOrder,
            type: ItemType::Curtain,
            parameters: CurtainParameters::fromArray([
                'width' => 200, 'height' => 250, 'material_width' => 300,
                'ribbon_code' => 'tuzky85', 'ratio' => 2,
                'kanyr' => false, 'kanyr_height' => null, 'right_left' => RightLeft::Right->value,
                'corners' => true, 'bottom' => false, 'narasit' => true, 'snails' => true,
            ]),
            material: 'voal bílý',
            count: 2,
            room: 'obývák',
        );

        $saveItem->handle(
            order: $submittedOrder,
            type: ItemType::Pillow,
            parameters: PillowParameters::fromArray([
                'width' => 45, 'height' => 45, 'stuffing' => true, 'zipper' => ZipperSide::Short->value,
            ]),
            material: 'bavlna režná',
            count: 4,
            notes: 'Prosím stejný odstín jako záclony.',
        );

        app(SubmitOrder::class)->handle(
            order: $submittedOrder,
            by: $lucie,
            deadline: now()->addMonth(),
            materialsNote: "voal bílý – 9 m\nbavlna režná – 3 m",
        );

        // Rozpracovaný draft
        Order::create(['user_id' => $lucie->id, 'name' => 'Penzion U Lípy – pokoje', 'status' => OrderStatus::Draft]);
    }
}
