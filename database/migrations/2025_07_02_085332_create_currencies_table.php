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
            $table->jsonb('name');
            $table->string('symbol', 10);
            $table->boolean('is_active')->default(true);

            $table->index('code');
        });

        DB::table('currencies')->insert([
            // Major Global Currencies
            ['code' => 'USD', 'name' => json_encode(['en' => 'US Dollar']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'EUR', 'name' => json_encode(['en' => 'Euro']), 'symbol' => '€', 'is_active' => true],
            ['code' => 'GBP', 'name' => json_encode(['en' => 'British Pound']), 'symbol' => '£', 'is_active' => true],
            ['code' => 'JPY', 'name' => json_encode(['en' => 'Japanese Yen']), 'symbol' => '¥', 'is_active' => true],
            ['code' => 'CNY', 'name' => json_encode(['en' => 'Chinese Yuan']), 'symbol' => '¥', 'is_active' => true],

            // North America
            ['code' => 'CAD', 'name' => json_encode(['en' => 'Canadian Dollar']), 'symbol' => 'C$', 'is_active' => true],
            ['code' => 'MXN', 'name' => json_encode(['en' => 'Mexican Peso']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'GTQ', 'name' => json_encode(['en' => 'Guatemalan Quetzal']), 'symbol' => 'Q', 'is_active' => true],
            ['code' => 'CRC', 'name' => json_encode(['en' => 'Costa Rican Colón']), 'symbol' => '₡', 'is_active' => true],
            ['code' => 'HNL', 'name' => json_encode(['en' => 'Honduran Lempira']), 'symbol' => 'L', 'is_active' => true],
            ['code' => 'NIO', 'name' => json_encode(['en' => 'Nicaraguan Córdoba']), 'symbol' => 'C$', 'is_active' => true],
            ['code' => 'PAB', 'name' => json_encode(['en' => 'Panamanian Balboa']), 'symbol' => 'B/.', 'is_active' => true],
            ['code' => 'BZD', 'name' => json_encode(['en' => 'Belize Dollar']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'SVC', 'name' => json_encode(['en' => 'Salvadoran Colón']), 'symbol' => '₡', 'is_active' => true],

            // South America
            ['code' => 'BRL', 'name' => json_encode(['en' => 'Brazilian Real']), 'symbol' => 'R$', 'is_active' => true],
            ['code' => 'ARS', 'name' => json_encode(['en' => 'Argentine Peso']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'CLP', 'name' => json_encode(['en' => 'Chilean Peso']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'COP', 'name' => json_encode(['en' => 'Colombian Peso']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'PEN', 'name' => json_encode(['en' => 'Peruvian Sol']), 'symbol' => 'S/', 'is_active' => true],
            ['code' => 'VES', 'name' => json_encode(['en' => 'Venezuelan Bolívar']), 'symbol' => 'Bs.S', 'is_active' => true],
            ['code' => 'UYU', 'name' => json_encode(['en' => 'Uruguayan Peso']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'PYG', 'name' => json_encode(['en' => 'Paraguayan Guaraní']), 'symbol' => '₲', 'is_active' => true],
            ['code' => 'BOB', 'name' => json_encode(['en' => 'Bolivian Boliviano']), 'symbol' => 'Bs', 'is_active' => true],
            ['code' => 'ECU', 'name' => json_encode(['en' => 'Ecuadorian Sucre']), 'symbol' => 'S/.', 'is_active' => true],
            ['code' => 'GYD', 'name' => json_encode(['en' => 'Guyanese Dollar']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'SRD', 'name' => json_encode(['en' => 'Surinamese Dollar']), 'symbol' => '$', 'is_active' => true],

            // Europe
            ['code' => 'CHF', 'name' => json_encode(['en' => 'Swiss Franc']), 'symbol' => 'CHF', 'is_active' => true],
            ['code' => 'NOK', 'name' => json_encode(['en' => 'Norwegian Krone']), 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'SEK', 'name' => json_encode(['en' => 'Swedish Krona']), 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'DKK', 'name' => json_encode(['en' => 'Danish Krone']), 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'ISK', 'name' => json_encode(['en' => 'Icelandic Króna']), 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'PLN', 'name' => json_encode(['en' => 'Polish Złoty']), 'symbol' => 'zł', 'is_active' => true],
            ['code' => 'CZK', 'name' => json_encode(['en' => 'Czech Koruna']), 'symbol' => 'Kč', 'is_active' => true],
            ['code' => 'HUF', 'name' => json_encode(['en' => 'Hungarian Forint']), 'symbol' => 'Ft', 'is_active' => true],
            ['code' => 'RON', 'name' => json_encode(['en' => 'Romanian Leu']), 'symbol' => 'lei', 'is_active' => true],
            ['code' => 'BGN', 'name' => json_encode(['en' => 'Bulgarian Lev']), 'symbol' => 'лв', 'is_active' => true],
            ['code' => 'HRK', 'name' => json_encode(['en' => 'Croatian Kuna']), 'symbol' => 'kn', 'is_active' => true],
            ['code' => 'RSD', 'name' => json_encode(['en' => 'Serbian Dinar']), 'symbol' => 'дин', 'is_active' => true],
            ['code' => 'BAM', 'name' => json_encode(['en' => 'Bosnia and Herzegovina Convertible Mark']), 'symbol' => 'KM', 'is_active' => true],
            ['code' => 'MKD', 'name' => json_encode(['en' => 'Macedonian Denar']), 'symbol' => 'ден', 'is_active' => true],
            ['code' => 'ALL', 'name' => json_encode(['en' => 'Albanian Lek']), 'symbol' => 'L', 'is_active' => true],
            ['code' => 'TRY', 'name' => json_encode(['en' => 'Turkish Lira']), 'symbol' => '₺', 'is_active' => true],
            ['code' => 'UAH', 'name' => json_encode(['en' => 'Ukrainian Hryvnia']), 'symbol' => '₴', 'is_active' => true],
            ['code' => 'BYN', 'name' => json_encode(['en' => 'Belarusian Ruble']), 'symbol' => 'Br', 'is_active' => true],
            ['code' => 'MDL', 'name' => json_encode(['en' => 'Moldovan Leu']), 'symbol' => 'L', 'is_active' => true],
            ['code' => 'RUB', 'name' => json_encode(['en' => 'Russian Ruble']), 'symbol' => '₽', 'is_active' => true],
            ['code' => 'GEL', 'name' => json_encode(['en' => 'Georgian Lari']), 'symbol' => '₾', 'is_active' => true],
            ['code' => 'AMD', 'name' => json_encode(['en' => 'Armenian Dram']), 'symbol' => '֏', 'is_active' => true],
            ['code' => 'AZN', 'name' => json_encode(['en' => 'Azerbaijani Manat']), 'symbol' => '₼', 'is_active' => true],

            // Asia-Pacific
            ['code' => 'AUD', 'name' => json_encode(['en' => 'Australian Dollar']), 'symbol' => 'A$', 'is_active' => true],
            ['code' => 'NZD', 'name' => json_encode(['en' => 'New Zealand Dollar']), 'symbol' => 'NZ$', 'is_active' => true],
            ['code' => 'INR', 'name' => json_encode(['en' => 'Indian Rupee']), 'symbol' => '₹', 'is_active' => true],
            ['code' => 'KRW', 'name' => json_encode(['en' => 'South Korean Won']), 'symbol' => '₩', 'is_active' => true],
            ['code' => 'SGD', 'name' => json_encode(['en' => 'Singapore Dollar']), 'symbol' => 'S$', 'is_active' => true],
            ['code' => 'HKD', 'name' => json_encode(['en' => 'Hong Kong Dollar']), 'symbol' => 'HK$', 'is_active' => true],
            ['code' => 'TWD', 'name' => json_encode(['en' => 'Taiwan Dollar']), 'symbol' => 'NT$', 'is_active' => true],
            ['code' => 'MYR', 'name' => json_encode(['en' => 'Malaysian Ringgit']), 'symbol' => 'RM', 'is_active' => true],
            ['code' => 'THB', 'name' => json_encode(['en' => 'Thai Baht']), 'symbol' => '฿', 'is_active' => true],
            ['code' => 'IDR', 'name' => json_encode(['en' => 'Indonesian Rupiah']), 'symbol' => 'Rp', 'is_active' => true],
            ['code' => 'PHP', 'name' => json_encode(['en' => 'Philippine Peso']), 'symbol' => '₱', 'is_active' => true],
            ['code' => 'VND', 'name' => json_encode(['en' => 'Vietnamese Dong']), 'symbol' => '₫', 'is_active' => true],
            ['code' => 'LAK', 'name' => json_encode(['en' => 'Lao Kip']), 'symbol' => '₭', 'is_active' => true],
            ['code' => 'KHR', 'name' => json_encode(['en' => 'Cambodian Riel']), 'symbol' => '៛', 'is_active' => true],
            ['code' => 'MMK', 'name' => json_encode(['en' => 'Myanmar Kyat']), 'symbol' => 'K', 'is_active' => true],
            ['code' => 'BND', 'name' => json_encode(['en' => 'Brunei Dollar']), 'symbol' => 'B$', 'is_active' => true],
            ['code' => 'LKR', 'name' => json_encode(['en' => 'Sri Lankan Rupee']), 'symbol' => '₨', 'is_active' => true],
            ['code' => 'NPR', 'name' => json_encode(['en' => 'Nepalese Rupee']), 'symbol' => '₨', 'is_active' => true],
            ['code' => 'BTN', 'name' => json_encode(['en' => 'Bhutanese Ngultrum']), 'symbol' => 'Nu.', 'is_active' => true],
            ['code' => 'MOP', 'name' => json_encode(['en' => 'Macanese Pataca']), 'symbol' => 'MOP$', 'is_active' => true],
            ['code' => 'KPW', 'name' => json_encode(['en' => 'North Korean Won']), 'symbol' => '₩', 'is_active' => true],
            ['code' => 'MNT', 'name' => json_encode(['en' => 'Mongolian Tugrik']), 'symbol' => '₮', 'is_active' => true],
            ['code' => 'PKR', 'name' => json_encode(['en' => 'Pakistani Rupee']), 'symbol' => '₨', 'is_active' => true],
            ['code' => 'BDT', 'name' => json_encode(['en' => 'Bangladeshi Taka']), 'symbol' => '৳', 'is_active' => true],
            ['code' => 'AFN', 'name' => json_encode(['en' => 'Afghan Afghani']), 'symbol' => '؋', 'is_active' => true],
            ['code' => 'UZS', 'name' => json_encode(['en' => 'Uzbekistani Som']), 'symbol' => "so'm", 'is_active' => true],
            ['code' => 'KZT', 'name' => json_encode(['en' => 'Kazakhstani Tenge']), 'symbol' => '₸', 'is_active' => true],
            ['code' => 'KGS', 'name' => json_encode(['en' => 'Kyrgyzstani Som']), 'symbol' => 'лв', 'is_active' => true],
            ['code' => 'TJS', 'name' => json_encode(['en' => 'Tajikistani Somoni']), 'symbol' => 'ЅМ', 'is_active' => true],
            ['code' => 'TMT', 'name' => json_encode(['en' => 'Turkmenistani Manat']), 'symbol' => 'T', 'is_active' => true],

            // Middle East
            ['code' => 'SAR', 'name' => json_encode(['en' => 'Saudi Riyal']), 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'AED', 'name' => json_encode(['en' => 'UAE Dirham']), 'symbol' => 'د.إ', 'is_active' => true],
            ['code' => 'QAR', 'name' => json_encode(['en' => 'Qatari Riyal']), 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'KWD', 'name' => json_encode(['en' => 'Kuwaiti Dinar']), 'symbol' => 'د.ك', 'is_active' => true],
            ['code' => 'BHD', 'name' => json_encode(['en' => 'Bahraini Dinar']), 'symbol' => 'ب.د', 'is_active' => true],
            ['code' => 'OMR', 'name' => json_encode(['en' => 'Omani Rial']), 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'ILS', 'name' => json_encode(['en' => 'Israeli New Shekel']), 'symbol' => '₪', 'is_active' => true],
            ['code' => 'JOD', 'name' => json_encode(['en' => 'Jordanian Dinar']), 'symbol' => 'د.ا', 'is_active' => true],
            ['code' => 'LBP', 'name' => json_encode(['en' => 'Lebanese Pound']), 'symbol' => '£', 'is_active' => true],
            ['code' => 'SYP', 'name' => json_encode(['en' => 'Syrian Pound']), 'symbol' => '£', 'is_active' => true],
            ['code' => 'IRR', 'name' => json_encode(['en' => 'Iranian Rial']), 'symbol' => '﷼', 'is_active' => true],
            ['code' => 'IQD', 'name' => json_encode(['en' => 'Iraqi Dinar']), 'symbol' => 'ع.د', 'is_active' => true],
            ['code' => 'YER', 'name' => json_encode(['en' => 'Yemeni Rial']), 'symbol' => '﷼', 'is_active' => true],

            // Africa
            ['code' => 'ZAR', 'name' => json_encode(['en' => 'South African Rand']), 'symbol' => 'R', 'is_active' => true],
            ['code' => 'NGN', 'name' => json_encode(['en' => 'Nigerian Naira']), 'symbol' => '₦', 'is_active' => true],
            ['code' => 'EGP', 'name' => json_encode(['en' => 'Egyptian Pound']), 'symbol' => '£', 'is_active' => true],
            ['code' => 'KES', 'name' => json_encode(['en' => 'Kenyan Shilling']), 'symbol' => 'KSh', 'is_active' => true],
            ['code' => 'TZS', 'name' => json_encode(['en' => 'Tanzanian Shilling']), 'symbol' => 'TSh', 'is_active' => true],
            ['code' => 'UGX', 'name' => json_encode(['en' => 'Ugandan Shilling']), 'symbol' => 'USh', 'is_active' => true],
            ['code' => 'ETB', 'name' => json_encode(['en' => 'Ethiopian Birr']), 'symbol' => 'Br', 'is_active' => true],
            ['code' => 'GHS', 'name' => json_encode(['en' => 'Ghanaian Cedi']), 'symbol' => '₵', 'is_active' => true],
            ['code' => 'DZD', 'name' => json_encode(['en' => 'Algerian Dinar']), 'symbol' => 'دج', 'is_active' => true],
            ['code' => 'MAD', 'name' => json_encode(['en' => 'Moroccan Dirham']), 'symbol' => 'DH', 'is_active' => true],
            ['code' => 'TND', 'name' => json_encode(['en' => 'Tunisian Dinar']), 'symbol' => 'د.ت', 'is_active' => true],
            ['code' => 'LYD', 'name' => json_encode(['en' => 'Libyan Dinar']), 'symbol' => 'ل.د', 'is_active' => true],
            ['code' => 'SDG', 'name' => json_encode(['en' => 'Sudanese Pound']), 'symbol' => 'ج.س.', 'is_active' => true],
            ['code' => 'ZMW', 'name' => json_encode(['en' => 'Zambian Kwacha']), 'symbol' => 'ZK', 'is_active' => true],
            ['code' => 'BWP', 'name' => json_encode(['en' => 'Botswana Pula']), 'symbol' => 'P', 'is_active' => true],
            ['code' => 'NAD', 'name' => json_encode(['en' => 'Namibian Dollar']), 'symbol' => 'N$', 'is_active' => true],
            ['code' => 'SZL', 'name' => json_encode(['en' => 'Swazi Lilangeni']), 'symbol' => 'L', 'is_active' => true],
            ['code' => 'LSL', 'name' => json_encode(['en' => 'Lesotho Loti']), 'symbol' => 'M', 'is_active' => true],
            ['code' => 'MZN', 'name' => json_encode(['en' => 'Mozambican Metical']), 'symbol' => 'MT', 'is_active' => true],
            ['code' => 'AOA', 'name' => json_encode(['en' => 'Angolan Kwanza']), 'symbol' => 'Kz', 'is_active' => true],
            ['code' => 'MWK', 'name' => json_encode(['en' => 'Malawian Kwacha']), 'symbol' => 'MK', 'is_active' => true],
            ['code' => 'MUR', 'name' => json_encode(['en' => 'Mauritian Rupee']), 'symbol' => '₨', 'is_active' => true],
            ['code' => 'SCR', 'name' => json_encode(['en' => 'Seychellois Rupee']), 'symbol' => '₨', 'is_active' => true],
            ['code' => 'MVR', 'name' => json_encode(['en' => 'Maldivian Rufiyaa']), 'symbol' => '.ރ', 'is_active' => true],
            ['code' => 'MGA', 'name' => json_encode(['en' => 'Malagasy Ariary']), 'symbol' => 'Ar', 'is_active' => true],
            ['code' => 'KMF', 'name' => json_encode(['en' => 'Comorian Franc']), 'symbol' => 'CF', 'is_active' => true],
            ['code' => 'RWF', 'name' => json_encode(['en' => 'Rwandan Franc']), 'symbol' => 'FRw', 'is_active' => true],
            ['code' => 'BIF', 'name' => json_encode(['en' => 'Burundian Franc']), 'symbol' => 'FBu', 'is_active' => true],
            ['code' => 'DJF', 'name' => json_encode(['en' => 'Djiboutian Franc']), 'symbol' => 'Fdj', 'is_active' => true],
            ['code' => 'SOS', 'name' => json_encode(['en' => 'Somali Shilling']), 'symbol' => 'S', 'is_active' => true],
            ['code' => 'ERN', 'name' => json_encode(['en' => 'Eritrean Nakfa']), 'symbol' => 'Nkf', 'is_active' => true],
            ['code' => 'XAF', 'name' => json_encode(['en' => 'Central African CFA Franc']), 'symbol' => 'FCFA', 'is_active' => true],
            ['code' => 'XOF', 'name' => json_encode(['en' => 'West African CFA Franc']), 'symbol' => 'CFA', 'is_active' => true],
            ['code' => 'CDF', 'name' => json_encode(['en' => 'Congolese Franc']), 'symbol' => 'FC', 'is_active' => true],
            ['code' => 'GMD', 'name' => json_encode(['en' => 'Gambian Dalasi']), 'symbol' => 'D', 'is_active' => true],
            ['code' => 'GNF', 'name' => json_encode(['en' => 'Guinean Franc']), 'symbol' => 'FG', 'is_active' => true],
            ['code' => 'LRD', 'name' => json_encode(['en' => 'Liberian Dollar']), 'symbol' => 'L$', 'is_active' => true],
            ['code' => 'SLL', 'name' => json_encode(['en' => 'Sierra Leonean Leone']), 'symbol' => 'Le', 'is_active' => true],
            ['code' => 'CVE', 'name' => json_encode(['en' => 'Cape Verdean Escudo']), 'symbol' => '$', 'is_active' => true],
            ['code' => 'STN', 'name' => json_encode(['en' => 'São Tomé and Príncipe Dobra']), 'symbol' => 'Db', 'is_active' => true],

            // Caribbean
            ['code' => 'JMD', 'name' => json_encode(['en' => 'Jamaican Dollar']), 'symbol' => 'J$', 'is_active' => true],
            ['code' => 'CUP', 'name' => json_encode(['en' => 'Cuban Peso']), 'symbol' => '₱', 'is_active' => true],
            ['code' => 'DOP', 'name' => json_encode(['en' => 'Dominican Peso']), 'symbol' => 'RD$', 'is_active' => true],
            ['code' => 'HTG', 'name' => json_encode(['en' => 'Haitian Gourde']), 'symbol' => 'G', 'is_active' => true],
            ['code' => 'BBD', 'name' => json_encode(['en' => 'Barbadian Dollar']), 'symbol' => 'Bds$', 'is_active' => true],
            ['code' => 'TTD', 'name' => json_encode(['en' => 'Trinidad and Tobago Dollar']), 'symbol' => 'TT$', 'is_active' => true],
            ['code' => 'XCD', 'name' => json_encode(['en' => 'East Caribbean Dollar']), 'symbol' => 'EC$', 'is_active' => true],
            ['code' => 'BSD', 'name' => json_encode(['en' => 'Bahamian Dollar']), 'symbol' => 'B$', 'is_active' => true],
            ['code' => 'KYD', 'name' => json_encode(['en' => 'Cayman Islands Dollar']), 'symbol' => 'CI$', 'is_active' => true],
            ['code' => 'BMD', 'name' => json_encode(['en' => 'Bermudian Dollar']), 'symbol' => 'BD$', 'is_active' => true],
            ['code' => 'AWG', 'name' => json_encode(['en' => 'Aruban Florin']), 'symbol' => 'ƒ', 'is_active' => true],
            ['code' => 'ANG', 'name' => json_encode(['en' => 'Netherlands Antillean Guilder']), 'symbol' => 'ƒ', 'is_active' => true],

            // Pacific
            ['code' => 'FJD', 'name' => json_encode(['en' => 'Fijian Dollar']), 'symbol' => 'FJ$', 'is_active' => true],
            ['code' => 'PGK', 'name' => json_encode(['en' => 'Papua New Guinean Kina']), 'symbol' => 'K', 'is_active' => true],
            ['code' => 'SBD', 'name' => json_encode(['en' => 'Solomon Islands Dollar']), 'symbol' => 'SI$', 'is_active' => true],
            ['code' => 'TOP', 'name' => json_encode(['en' => 'Tongan Paanga']), 'symbol' => 'T$', 'is_active' => true],
            ['code' => 'VUV', 'name' => json_encode(['en' => 'Vanuatu Vatu']), 'symbol' => 'VT', 'is_active' => true],
            ['code' => 'WST', 'name' => json_encode(['en' => 'Samoan Tala']), 'symbol' => 'WS$', 'is_active' => true],
            ['code' => 'XPF', 'name' => json_encode(['en' => 'CFP Franc']), 'symbol' => '₣', 'is_active' => true],
            ['code' => 'NCO', 'name' => json_encode(['en' => 'New Caledonian Franc']), 'symbol' => '₣', 'is_active' => true],

            // Special Currencies
            ['code' => 'XDR', 'name' => json_encode(['en' => 'Special Drawing Rights']), 'symbol' => 'SDR', 'is_active' => true],
            ['code' => 'XAU', 'name' => json_encode(['en' => 'Gold (troy ounce)']), 'symbol' => 'Au', 'is_active' => true],
            ['code' => 'XAG', 'name' => json_encode(['en' => 'Silver (troy ounce)']), 'symbol' => 'Ag', 'is_active' => true],
            ['code' => 'XPT', 'name' => json_encode(['en' => 'Platinum (troy ounce)']), 'symbol' => 'Pt', 'is_active' => true],
            ['code' => 'XPD', 'name' => json_encode(['en' => 'Palladium (troy ounce)']), 'symbol' => 'Pd', 'is_active' => true],

            // Cryptocurrencies (major ones)
            ['code' => 'BTC', 'name' => json_encode(['en' => 'Bitcoin']), 'symbol' => '₿', 'is_active' => true],
            ['code' => 'ETH', 'name' => json_encode(['en' => 'Ethereum']), 'symbol' => 'Ξ', 'is_active' => true],
            ['code' => 'LTC', 'name' => json_encode(['en' => 'Litecoin']), 'symbol' => 'Ł', 'is_active' => true],
            ['code' => 'XRP', 'name' => json_encode(['en' => 'Ripple']), 'symbol' => 'XRP', 'is_active' => true],
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
