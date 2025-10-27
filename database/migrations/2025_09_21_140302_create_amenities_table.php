<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('icon_library'); // e.g., material, fa, ionicons, etc.
            $table->string('category'); // Property, Unit, Both
            $table->string('type')->default('general'); // general, safety, accessibility
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seeding amenities
        \DB::table('amenities')->insert([
            ['slug' => 'wi-fi', 'name' => json_encode(['en' => 'Wi-Fi']), 'icon' => 'IosWifi', 'icon_library' => 'ionicons5', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'air-conditioning', 'name' => json_encode(['en' => 'Air Conditioning']), 'icon' => 'AcUnitFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'heating', 'name' => json_encode(['en' => 'Heating']), 'icon' => 'FireplaceFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'kitchen', 'name' => json_encode(['en' => 'Kitchen']), 'icon' => 'ToolsKitchen', 'icon_library' => 'tabler', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'parking', 'name' => json_encode(['en' => 'Parking']), 'icon' => 'LocalParkingFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['slug' => 'pool', 'name' => json_encode(['en' => 'Pool']), 'icon' => 'PoolFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['slug' => 'washer', 'name' => json_encode(['en' => 'Washer']), 'icon' => 'LocalLaundryServiceRound', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'dryer', 'name' => json_encode(['en' => 'Dryer']), 'icon' => 'DryCleaningFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'tv-general', 'name' => json_encode(['en' => 'TV']), 'icon' => 'MdTv', 'icon_library' => 'ionicons4', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['slug' => 'gym', 'name' => json_encode(['en' => 'Gym']), 'icon' => 'FitnessCenterFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['slug' => 'breakfast', 'name' => json_encode(['en' => 'Breakfast']), 'icon' => 'FreeBreakfastFilled',  'icon_library' => 'material', 'category' =>  'Property', 'type' => 'general', 'is_active' => true],
            ['slug' => 'pets-allowed', 'name' => json_encode(['en' => 'Pets Allowed']), 'icon' => 'PetsFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],

            // Room amenity types
            ['slug' => 'coffee-tea-maker', 'name' => json_encode(['en' => 'Coffee/Tea maker']), 'icon' => 'Coffee', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'mini-bar', 'name' => json_encode(['en' => 'Mini-bar']), 'icon' => 'DrinkWine16Filled', 'icon_library' => 'fluent', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'shower', 'name' => json_encode(['en' => 'Shower']), 'icon' => 'Shower', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'bath', 'name' => json_encode(['en' => 'Bath']), 'icon' => 'Bath', 'icon_library' => 'Bath', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'safe-deposit-box', 'name' => json_encode(['en' => 'Safe Deposit Box']), 'icon' => 'PiggyBank', 'icon_library' => 'carbon', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'pay-per-view-channels', 'name' => json_encode(['en' => 'Pay-per-view Channels']), 'icon' => 'LiveTvSharp', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'tv-room', 'name' => json_encode(['en' => 'TV']), 'icon' => 'MdTv', 'icon_library' => 'ionicons4', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'telephone', 'name' => json_encode(['en' => 'Telephone']), 'icon' => 'LocalPhoneFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'fax', 'name' => json_encode(['en' => 'Fax']), 'icon' => 'Fax', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'airconditioning', 'name' => json_encode(['en' => 'Airconditioning']), 'icon' => 'AcUnitFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'hair-dryer', 'name' => json_encode(['en' => 'Hair Dryer']), 'icon' => 'IosCheckmarkCircleOutline', 'icon_library' => 'ionicons4', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'wake-up-service', 'name' => json_encode(['en' => 'Wake Up Service/Alarm-clock']), 'icon' => 'BedOutlined', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'hot-tub', 'name' => json_encode(['en' => 'Hot Tub']), 'icon' => 'HotTubFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'clothing-iron', 'name' => json_encode(['en' => 'Clothing Iron']), 'icon' => 'IronRound', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'kitchenette', 'name' => json_encode(['en' => 'Kitchenette']), 'icon' => 'ToolsKitchen', 'icon_library' => 'tabler', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'balcony', 'name' => json_encode(['en' => 'Balcony']), 'icon' => 'BalconyFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['slug' => 'trouser-press', 'name' => json_encode(['en' => 'Trouser Press']), 'icon' => 'CompressOutlined', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
