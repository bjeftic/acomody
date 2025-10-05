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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
        });

        DB::table('currencies')->insert([
            // Major Global Currencies
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_active' => true],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'is_active' => true],

            // North America
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'is_active' => true],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'GTQ', 'name' => 'Guatemalan Quetzal', 'symbol' => 'Q', 'is_active' => true],
            ['code' => 'CRC', 'name' => 'Costa Rican Colón', 'symbol' => '₡', 'is_active' => true],
            ['code' => 'HNL', 'name' => 'Honduran Lempira', 'symbol' => 'L', 'is_active' => true],
            ['code' => 'NIO', 'name' => 'Nicaraguan Córdoba', 'symbol' => 'C$', 'is_active' => true],
            ['code' => 'PAB', 'name' => 'Panamanian Balboa', 'symbol' => 'B/.', 'is_active' => true],
            ['code' => 'BZD', 'name' => 'Belize Dollar', 'symbol' => '$', 'is_active' => true],
            ['code' => 'SVC', 'name' => 'Salvadoran Colón', 'symbol' => '₡', 'is_active' => true],

            // South America
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'is_active' => true],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'is_active' => true],
            ['code' => 'VES', 'name' => 'Venezuelan Bolívar', 'symbol' => 'Bs.S', 'is_active' => true],
            ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'PYG', 'name' => 'Paraguayan Guaraní', 'symbol' => '₲', 'is_active' => true],
            ['code' => 'BOB', 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs', 'is_active' => true],
            ['code' => 'ECU', 'name' => 'Ecuadorian Sucre', 'symbol' => 'S/.', 'is_active' => true],
            ['code' => 'GYD', 'name' => 'Guyanese Dollar', 'symbol' => '$', 'is_active' => true],
            ['code' => 'SRD', 'name' => 'Surinamese Dollar', 'symbol' => '$', 'is_active' => true],

            // Europe
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'is_active' => true],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'ISK', 'name' => 'Icelandic Króna', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'PLN', 'name' => 'Polish Złoty', 'symbol' => 'zł', 'is_active' => true],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'is_active' => true],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'is_active' => true],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'lei', 'is_active' => true],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => 'лв', 'is_active' => true],
            ['code' => 'HRK', 'name' => 'Croatian Kuna', 'symbol' => 'kn', 'is_active' => true],
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'дин', 'is_active' => true],
            ['code' => 'BAM', 'name' => 'Bosnia and Herzegovina Convertible Mark', 'symbol' => 'KM', 'is_active' => true],
            ['code' => 'MKD', 'name' => 'Macedonian Denar', 'symbol' => 'ден', 'is_active' => true],
            ['code' => 'ALL', 'name' => 'Albanian Lek', 'symbol' => 'L', 'is_active' => true],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺', 'is_active' => true],
            ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia', 'symbol' => '₴', 'is_active' => true],
            ['code' => 'BYN', 'name' => 'Belarusian Ruble', 'symbol' => 'Br', 'is_active' => true],
            ['code' => 'MDL', 'name' => 'Moldovan Leu', 'symbol' => 'L', 'is_active' => true],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'is_active' => true],
            ['code' => 'GEL', 'name' => 'Georgian Lari', 'symbol' => '₾', 'is_active' => true],
            ['code' => 'AMD', 'name' => 'Armenian Dram', 'symbol' => '֏', 'is_active' => true],
            ['code' => 'AZN', 'name' => 'Azerbaijani Manat', 'symbol' => '₼', 'is_active' => true],

            // Asia-Pacific
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'is_active' => true],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'is_active' => true],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'is_active' => true],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'is_active' => true],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'is_active' => true],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'is_active' => true],
            ['code' => 'TWD', 'name' => 'Taiwan Dollar', 'symbol' => 'NT$', 'is_active' => true],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'is_active' => true],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'is_active' => true],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'is_active' => true],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱', 'is_active' => true],
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'is_active' => true],
            ['code' => 'LAK', 'name' => 'Lao Kip', 'symbol' => '₭', 'is_active' => true],
            ['code' => 'KHR', 'name' => 'Cambodian Riel', 'symbol' => '៛', 'is_active' => true],
            ['code' => 'MMK', 'name' => 'Myanmar Kyat', 'symbol' => 'K', 'is_active' => true],
            ['code' => 'BND', 'name' => 'Brunei Dollar', 'symbol' => 'B$', 'is_active' => true],
            ['code' => 'LKR', 'name' => 'Sri Lankan Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'NPR', 'name' => 'Nepalese Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'BTN', 'name' => 'Bhutanese Ngultrum', 'symbol' => 'Nu.', 'is_active' => true],
            ['code' => 'MOP', 'name' => 'Macanese Pataca', 'symbol' => 'MOP$', 'is_active' => true],
            ['code' => 'KPW', 'name' => 'North Korean Won', 'symbol' => '₩', 'is_active' => true],
            ['code' => 'MNT', 'name' => 'Mongolian Tugrik', 'symbol' => '₮', 'is_active' => true],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'is_active' => true],
            ['code' => 'AFN', 'name' => 'Afghan Afghani', 'symbol' => '؋', 'is_active' => true],
            ['code' => 'UZS', 'name' => 'Uzbekistani Som', 'symbol' => "so'm", 'is_active' => true],
            ['code' => 'KZT', 'name' => 'Kazakhstani Tenge', 'symbol' => '₸', 'is_active' => true],
            ['code' => 'KGS', 'name' => 'Kyrgyzstani Som', 'symbol' => 'лв', 'is_active' => true],
            ['code' => 'TJS', 'name' => 'Tajikistani Somoni', 'symbol' => 'ЅМ', 'is_active' => true],
            ['code' => 'TMT', 'name' => 'Turkmenistani Manat', 'symbol' => 'T', 'is_active' => true],

            // Middle East
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_active' => true],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'is_active' => true],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'ب.د', 'is_active' => true],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'ILS', 'name' => 'Israeli New Shekel', 'symbol' => '₪', 'is_active' => true],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar', 'symbol' => 'د.ا', 'is_active' => true],
            ['code' => 'LBP', 'name' => 'Lebanese Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'IRR', 'name' => 'Iranian Rial', 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => 'ع.د', 'is_active' => true],
            ['code' => 'YER', 'name' => 'Yemeni Rial', 'symbol' => '﷼', 'is_active' => true],

            // Africa
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'is_active' => true],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'is_active' => true],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'is_active' => true],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'is_active' => true],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'is_active' => true],
            ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'is_active' => true],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => '₵', 'is_active' => true],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'دج', 'is_active' => true],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'DH', 'is_active' => true],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت', 'is_active' => true],
            ['code' => 'LYD', 'name' => 'Libyan Dinar', 'symbol' => 'ل.د', 'is_active' => true],
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'symbol' => 'ج.س.', 'is_active' => true],
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'is_active' => true],
            ['code' => 'BWP', 'name' => 'Botswana Pula', 'symbol' => 'P', 'is_active' => true],
            ['code' => 'NAD', 'name' => 'Namibian Dollar', 'symbol' => 'N$', 'is_active' => true],
            ['code' => 'SZL', 'name' => 'Swazi Lilangeni', 'symbol' => 'L', 'is_active' => true],
            ['code' => 'LSL', 'name' => 'Lesotho Loti', 'symbol' => 'M', 'is_active' => true],
            ['code' => 'MZN', 'name' => 'Mozambican Metical', 'symbol' => 'MT', 'is_active' => true],
            ['code' => 'AOA', 'name' => 'Angolan Kwanza', 'symbol' => 'Kz', 'is_active' => true],
            ['code' => 'MWK', 'name' => 'Malawian Kwacha', 'symbol' => 'MK', 'is_active' => true],
            ['code' => 'MUR', 'name' => 'Mauritian Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'SCR', 'name' => 'Seychellois Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'MVR', 'name' => 'Maldivian Rufiyaa', 'symbol' => '.ރ', 'is_active' => true],
            ['code' => 'MGA', 'name' => 'Malagasy Ariary', 'symbol' => 'Ar', 'is_active' => true],
            ['code' => 'KMF', 'name' => 'Comorian Franc', 'symbol' => 'CF', 'is_active' => true],
            ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'FRw', 'is_active' => true],
            ['code' => 'BIF', 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'is_active' => true],
            ['code' => 'DJF', 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'is_active' => true],
            ['code' => 'SOS', 'name' => 'Somali Shilling', 'symbol' => 'S', 'is_active' => true],
            ['code' => 'ERN', 'name' => 'Eritrean Nakfa', 'symbol' => 'Nkf', 'is_active' => true],
            ['code' => 'XAF', 'name' => 'Central African CFA Franc', 'symbol' => 'FCFA', 'is_active' => true],
            ['code' => 'XOF', 'name' => 'West African CFA Franc', 'symbol' => 'CFA', 'is_active' => true],
            ['code' => 'CDF', 'name' => 'Congolese Franc', 'symbol' => 'FC', 'is_active' => true],
            ['code' => 'GMD', 'name' => 'Gambian Dalasi', 'symbol' => 'D', 'is_active' => true],
            ['code' => 'GNF', 'name' => 'Guinean Franc', 'symbol' => 'FG', 'is_active' => true],
            ['code' => 'LRD', 'name' => 'Liberian Dollar', 'symbol' => 'L$', 'is_active' => true],
            ['code' => 'SLL', 'name' => 'Sierra Leonean Leone', 'symbol' => 'Le', 'is_active' => true],
            ['code' => 'CVE', 'name' => 'Cape Verdean Escudo', 'symbol' => '$', 'is_active' => true],
            ['code' => 'STN', 'name' => 'São Tomé and Príncipe Dobra', 'symbol' => 'Db', 'is_active' => true],

            // Caribbean
            ['code' => 'JMD', 'name' => 'Jamaican Dollar', 'symbol' => 'J$', 'is_active' => true],
            ['code' => 'CUP', 'name' => 'Cuban Peso', 'symbol' => '₱', 'is_active' => true],
            ['code' => 'DOP', 'name' => 'Dominican Peso', 'symbol' => 'RD$', 'is_active' => true],
            ['code' => 'HTG', 'name' => 'Haitian Gourde', 'symbol' => 'G', 'is_active' => true],
            ['code' => 'BBD', 'name' => 'Barbadian Dollar', 'symbol' => 'Bds$', 'is_active' => true],
            ['code' => 'TTD', 'name' => 'Trinidad and Tobago Dollar', 'symbol' => 'TT$', 'is_active' => true],
            ['code' => 'XCD', 'name' => 'East Caribbean Dollar', 'symbol' => 'EC$', 'is_active' => true],
            ['code' => 'BSD', 'name' => 'Bahamian Dollar', 'symbol' => 'B$', 'is_active' => true],
            ['code' => 'KYD', 'name' => 'Cayman Islands Dollar', 'symbol' => 'CI$', 'is_active' => true],
            ['code' => 'BMD', 'name' => 'Bermudian Dollar', 'symbol' => 'BD$', 'is_active' => true],
            ['code' => 'AWG', 'name' => 'Aruban Florin', 'symbol' => 'ƒ', 'is_active' => true],
            ['code' => 'ANG', 'name' => 'Netherlands Antillean Guilder', 'symbol' => 'ƒ', 'is_active' => true],

            // Pacific
            ['code' => 'FJD', 'name' => 'Fijian Dollar', 'symbol' => 'FJ$', 'is_active' => true],
            ['code' => 'PGK', 'name' => 'Papua New Guinean Kina', 'symbol' => 'K', 'is_active' => true],
            ['code' => 'SBD', 'name' => 'Solomon Islands Dollar', 'symbol' => 'SI$', 'is_active' => true],
            ['code' => 'TOP', 'name' => 'Tongan Paanga', 'symbol' => 'T$', 'is_active' => true],
            ['code' => 'VUV', 'name' => 'Vanuatu Vatu', 'symbol' => 'VT', 'is_active' => true],
            ['code' => 'WST', 'name' => 'Samoan Tala', 'symbol' => 'WS$', 'is_active' => true],
            ['code' => 'XPF', 'name' => 'CFP Franc', 'symbol' => '₣', 'is_active' => true],
            ['code' => 'NCO', 'name' => 'New Caledonian Franc', 'symbol' => '₣', 'is_active' => true],

            // Special Currencies
            ['code' => 'XDR', 'name' => 'Special Drawing Rights', 'symbol' => 'SDR', 'is_active' => true],
            ['code' => 'XAU', 'name' => 'Gold (troy ounce)', 'symbol' => 'Au', 'is_active' => true],
            ['code' => 'XAG', 'name' => 'Silver (troy ounce)', 'symbol' => 'Ag', 'is_active' => true],
            ['code' => 'XPT', 'name' => 'Platinum (troy ounce)', 'symbol' => 'Pt', 'is_active' => true],
            ['code' => 'XPD', 'name' => 'Palladium (troy ounce)', 'symbol' => 'Pd', 'is_active' => true],

            // Cryptocurrencies (major ones)
            ['code' => 'BTC', 'name' => 'Bitcoin', 'symbol' => '₿', 'is_active' => true],
            ['code' => 'ETH', 'name' => 'Ethereum', 'symbol' => 'Ξ', 'is_active' => true],
            ['code' => 'LTC', 'name' => 'Litecoin', 'symbol' => 'Ł', 'is_active' => true],
            ['code' => 'XRP', 'name' => 'Ripple', 'symbol' => 'XRP', 'is_active' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
