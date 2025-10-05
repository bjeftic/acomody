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
            ['name' => 'Wi-Fi', 'icon' => 'IosWifi', 'icon_library' => 'ionicons5', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Air Conditioning', 'icon' => 'AcUnitFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Heating', 'icon' => 'FireplaceFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Kitchen', 'icon' => 'ToolsKitchen', 'icon_library' => 'tabler', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Parking', 'icon' => 'LocalParkingFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['name' => 'Pool', 'icon' => 'PoolFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['name' => 'Washer', 'icon' => 'LocalLaundryServiceRound', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Dryer', 'icon' => 'DryCleaningFilled', 'icon_library' => 'material', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'TV', 'icon' => 'MdTv', 'icon_library' => 'ionicons4', 'category' => 'Both', 'type' => 'general', 'is_active' => true],
            ['name' => 'Gym', 'icon' => 'FitnessCenterFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],
            ['name' => 'Breakfast', 'icon' => 'FreeBreakfastFilled',  'icon_library' => 'material', 'category' =>  'Property', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Pets Allowed', 'icon' => 'PetsFilled', 'icon_library' => 'material', 'category' => 'Property', 'type' => 'general', 'is_active' => true],

            // Room amenity types
            ['name' => 'Coffee/Tea maker', 'icon' => 'Coffee', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' => 'Mini-bar', 'icon' => 'DrinkWine16Filled', 'icon_library' => 'fluent', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' => 'Shower', 'icon' => 'Shower', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' => 'Bath', 'icon' => 'Bath', 'icon_library' => 'Bath', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' => 'Safe Deposit Box', 'icon' => 'PiggyBank', 'icon_library' => 'carbon', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Pay-per-view Channels', 'icon' => 'LiveTvSharp', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'TV', 'icon' => 'MdTv', 'icon_library' => 'ionicons4', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Telephone', 'icon' => 'LocalPhoneFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Fax', 'icon' => 'Fax', 'icon_library' => 'fa', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Airconditioning', 'icon' => 'AcUnitFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Hair Dryer', 'icon' => 'IosCheckmarkCircleOutline', 'icon_library' => 'ionicons4', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Wake Up Service/Alarm-clock', 'icon' => 'BedOutlined', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Hot Tub', 'icon' => 'HotTubFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Clothing Iron', 'icon' => 'IronRound', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Kitchenette', 'icon' => 'ToolsKitchen', 'icon_library' => 'tabler', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Balcony', 'icon' => 'BalconyFilled', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
            ['name' =>  'Trouser Press', 'icon' => 'CompressOutlined', 'icon_library' => 'material', 'category' => 'Unit', 'type' => 'general', 'is_active' => true],
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
