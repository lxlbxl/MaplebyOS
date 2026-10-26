<?php

namespace Modules\Operations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Operations\Entities\Port;
use Modules\Operations\Entities\PurchasePlatform;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Entities\VehicleMake;
use Modules\Operations\Entities\VehicleModel;

/**
 * Starter catalog for smart suggestions. Aliases include the misspellings seen
 * in the tracking sheet so imports and typing resolve to one canonical name.
 * Safe to run repeatedly: it only adds what's missing and merges aliases.
 */
class OperationsCatalogSeeder extends Seeder
{
    public const MAKES = [
        'TOYOTA' => [[], [
            'COROLLA' => ['CORROLA', 'COROLA'], 'CAMRY' => ['CARMY', 'CAMERY'], 'RAV4' => ['RAV 4', 'RAV-4'],
            'HIGHLANDER' => ['HIGHLANDA'], 'SIENNA' => ['SIENA'], 'VENZA' => [], 'MATRIX' => [], 'TACOMA' => [],
            'YARIS' => [], 'PRIUS' => [], 'TUNDRA' => [], '4RUNNER' => ['4 RUNNER', 'FOURRUNNER'], 'AVALON' => [],
            'SEQUOIA' => [], 'LAND CRUISER' => ['LANDCRUISER'], 'C-HR' => ['CHR'], 'COROLLA CROSS' => [],
        ]],
        'LEXUS' => [['LEXUX', 'LEXSUS'], [
            'RX 350' => ['RX350', 'RX-350'], 'RX 300' => ['RX300'], 'RX 330' => ['RX330'], 'RX' => [],
            'ES 350' => ['ES350'], 'ES 330' => ['ES330'], 'GS 350' => ['GS350'], 'IS' => [], 'IS 250' => ['IS250'],
            'GX 460' => ['GX460'], 'LX 570' => ['LX570'], 'NX' => [], 'NX 200T' => ['NX200T'],
        ]],
        'HONDA' => [[], [
            'CR-V' => ['CRV', 'CR V'], 'ACCORD' => [], 'CIVIC' => [], 'ODYSSEY' => ['ODYSEY', 'ODESSEY'],
            'PILOT' => [], 'HR-V' => ['HRV'], 'FIT' => [], 'RIDGELINE' => [],
        ]],
        'ACURA' => [[], ['MDX' => [], 'RDX' => [], 'TLX' => [], 'TL' => [], 'ILX' => []]],
        'HYUNDAI' => [['HYUDAI', 'HYUNDIA', 'HUNDAI', 'HYNDAI'], [
            'ELANTRA' => ['ELANTA'], 'SONATA' => [], 'SANTA FE' => ['SANTAFE', 'SANTA-FE'],
            'TUCSON' => ['TUCON', 'TUSCON'], 'ACCENT' => [], 'KONA' => [], 'PALISADE' => [], 'VELOSTER' => [],
        ]],
        'KIA' => [[], [
            'SOUL' => [], 'OPTIMA' => [], 'RIO' => [], 'FORTE' => [], 'SORENTO' => ['SORRENTO'],
            'SPORTAGE' => [], 'TELLURIDE' => [], 'SEDONA' => [], 'K5' => [],
        ]],
        'NISSAN' => [['NISAN'], [
            'ALTIMA' => [], 'SENTRA' => [], 'VERSA' => [], 'JUKE' => [], 'ROGUE' => [], 'MURANO' => [],
            'PATHFINDER' => ['PATHFINER', 'PATHFINDA'], 'MAXIMA' => [], 'ARMADA' => [], 'FRONTIER' => [],
        ]],
        'INFINITI' => [['INFINITY', 'INFINITE'], [
            'QX60' => ['QX 60'], 'QX56' => ['QX 56'], 'QX80' => ['QX 80'], 'Q50' => ['Q 50'], 'JX35' => ['JX 35'], 'FX35' => ['FX 35'],
        ]],
        'FORD' => [[], [
            'F-150' => ['F150', 'F 150'], 'ESCAPE' => [], 'EXPLORER' => [], 'EDGE' => [], 'FUSION' => [],
            'FOCUS' => [], 'EXPEDITION' => [], 'MUSTANG' => [], 'RANGER' => [], 'TRANSIT' => [],
        ]],
        'LINCOLN' => [[], ['MKX' => [], 'NAVIGATOR' => [], 'MKZ' => [], 'AVIATOR' => []]],
        'CHEVROLET' => [['CHEVY', 'CHEVROLLET'], [
            'EQUINOX' => [], 'MALIBU' => [], 'CRUZE' => [], 'TAHOE' => [], 'SILVERADO' => [], 'TRAVERSE' => [], 'CAMARO' => [],
        ]],
        'GMC' => [[], ['TERRAIN' => ['TERAN', 'TERRAN'], 'ACADIA' => [], 'YUKON' => [], 'SIERRA' => []]],
        'CADILLAC' => [[], ['ESCALADE' => [], 'SRX' => [], 'XT5' => []]],
        'BUICK' => [[], ['ENCLAVE' => [], 'ENCORE' => [], 'LACROSSE' => []]],
        'DODGE' => [[], [
            'GRAND CARAVAN' => [], 'CARAVAN' => ['CANAVAN', 'CARAVAAN'], 'JOURNEY' => [], 'CHARGER' => [],
            'CHALLENGER' => [], 'DURANGO' => [],
        ]],
        'CHRYSLER' => [[], ['TOWN & COUNTRY' => ['TOWN AND COUNTRY'], 'PACIFICA' => [], '300' => []]],
        'JEEP' => [[], ['WRANGLER' => [], 'GRAND CHEROKEE' => [], 'CHEROKEE' => [], 'COMPASS' => [], 'PATRIOT' => []]],
        'RAM' => [[], ['1500' => [], '2500' => []]],
        'MERCEDES-BENZ' => [['MERCEDES', 'BENZ', 'MERCEDES BENZ', 'MERCEDEZ', 'GWAGON', 'G-WAGON'], [
            'C 300' => ['C300'], 'C 350' => ['C350', 'C-350'], 'E 350' => ['E350'], 'GLK' => ['GLK 350', 'GLK350'],
            'ML 350' => ['ML350', 'M CLASS 350', 'M-CLASS'], 'GLE' => [], 'GLE 350' => ['GLE350'], 'GLE 400' => ['GLE400'],
            'GLE 450' => ['GLE450'], 'GLA' => [], 'GLC' => [], 'GL 450' => ['GL450'], 'G-CLASS' => ['G WAGON', 'GWAGON', 'G63'],
            'SPRINTER' => [],
        ]],
        'BMW' => [[], ['X1' => [], 'X3' => [], 'X5' => [], 'X6' => [], '3 SERIES' => ['328I', '328'], '5 SERIES' => ['528I', '535I']]],
        'AUDI' => [[], ['Q5' => [], 'Q7' => [], 'A4' => [], 'A6' => []]],
        'VOLKSWAGEN' => [['VW'], ['JETTA' => [], 'PASSAT' => [], 'TIGUAN' => [], 'GOLF' => [], 'ATLAS' => []]],
        'PORSCHE' => [[], ['CAYENNE' => ['CAYENE', 'CAYANNE'], 'MACAN' => [], 'PANAMERA' => []]],
        'LAND ROVER' => [['LANDROVER', 'RANGE', 'RANGE ROVER'], [
            'RANGE ROVER' => ['ROVER', 'RANGEROVER'], 'RANGE ROVER SPORT' => [], 'RANGE ROVER EVOQUE' => ['EVOQUE'],
            'DISCOVERY' => [], 'DEFENDER' => [],
        ]],
        'MAZDA' => [[], ['CX-5' => ['CX5', 'CX 5'], 'CX-9' => ['CX9', 'CX 9'], 'MAZDA3' => ['3', 'MAZDA 3'], 'MAZDA6' => ['6', 'MAZDA 6'], 'CX-30' => ['CX30']]],
        'SUBARU' => [[], ['CROSSTREK' => [], 'FORESTER' => [], 'OUTBACK' => [], 'IMPREZA' => [], 'LEGACY' => []]],
        'MITSUBISHI' => [[], ['OUTLANDER' => [], 'LANCER' => [], 'RVR' => []]],
        'MINI' => [[], ['COOPER' => ['COPPER', 'COOPPER'], 'COUNTRYMAN' => []]],
        'PONTIAC' => [[], ['VIBE' => [], 'G6' => []]],
        'VOLVO' => [[], ['XC90' => ['XC 90'], 'XC60' => ['XC 60']]],
        'TESLA' => [[], ['MODEL 3' => [], 'MODEL Y' => [], 'MODEL S' => [], 'MODEL X' => []]],
    ];

    public const SHIPPING_LINES = [
        'HAPAG-LLOYD' => [
            'scac' => 'HLCU',
            'aliases' => ['HAPAG LLOYD', 'HAPAGLLOYD', 'HAPAG', 'HAPALIOYD', 'HAPAG-LIOYD', 'HAPAGLIOYD', 'HAPAL LOYD'],
            'prefixes' => ['HLCU', 'HLBU', 'HLXU', 'HAMU', 'UACU', 'UETU'],
            'tracking_url' => 'https://www.hapag-lloyd.com/en/online-business/track/track-by-container-solution.html?container={number}',
        ],
        'MSC' => [
            'scac' => 'MSCU',
            'aliases' => ['MEDITERRANEAN SHIPPING COMPANY', 'M.S.C', 'M S C'],
            'prefixes' => ['MSCU', 'MEDU', 'MSDU', 'MSMU', 'MSNU', 'MSBU'],
            'tracking_url' => 'https://www.msc.com/en/track-a-shipment?trackingNumber={number}',
        ],
        'GRIMALDI' => [
            'scac' => 'GRIM',
            'aliases' => ['GRIMALDI LINES', 'GRIMALDY', 'GRIMALDI GROUP'],
            'prefixes' => ['GCNU', 'GCXU', 'ACLU'],
            'tracking_url' => 'https://www.grimaldi.napoli.it/en/tracking.html',
        ],
        'MAERSK' => [
            'scac' => 'MAEU',
            'aliases' => ['MAERSK LINE', 'MEARSK'],
            'prefixes' => ['MSKU', 'MRKU', 'MAEU', 'MRSU'],
            'tracking_url' => 'https://www.maersk.com/tracking/{number}',
        ],
        'CMA CGM' => [
            'scac' => 'CMDU',
            'aliases' => ['CMA', 'CMACGM', 'CMA-CGM'],
            'prefixes' => ['CMAU', 'CGMU', 'APZU'],
            'tracking_url' => 'https://www.cma-cgm.com/ebusiness/tracking/search?SearchBy=Container&Reference={number}',
        ],
        'ONE' => ['scac' => 'ONEY', 'aliases' => ['OCEAN NETWORK EXPRESS'], 'prefixes' => ['ONEU'], 'tracking_url' => null],
        'COSCO' => ['scac' => 'COSU', 'aliases' => ['COSCO SHIPPING'], 'prefixes' => ['CSNU', 'CBHU'], 'tracking_url' => null],
        'EVERGREEN' => ['scac' => 'EGLV', 'aliases' => ['EVER GREEN'], 'prefixes' => ['EGHU', 'EMCU', 'EISU'], 'tracking_url' => null],
        'ZIM' => ['scac' => 'ZIMU', 'aliases' => [], 'prefixes' => ['ZIMU', 'ZCSU'], 'tracking_url' => null],
    ];

    public const PLATFORMS = [
        'COPART' => ['COPART.COM', 'COPPART'],
        'IAA' => ['IAAI', 'INSURANCE AUTO AUCTIONS', 'IAA AUCTION'],
        'MANHEIM' => ['MANHEIM AUCTION', 'MANHIEM'],
        'ACV AUCTIONS' => ['ACV'],
        'ADESA' => [],
        'OPENLANE' => ['OPEN LANE', 'TRADEREV'],
        'IMPACT AUTO AUCTIONS' => ['IMPACT'],
        'FACEBOOK MARKETPLACE' => ['FACEBOOK', 'FB MARKETPLACE', 'FB'],
        'KIJIJI' => ['KIJIJI AUTOS'],
        'DEALER' => ['DEALERSHIP'],
        'PRIVATE SELLER' => ['PRIVATE'],
        'CUSTOMER SUPPLIED' => ['CUSTOMER', 'OWN CAR', 'CLIENT CAR'],
    ];

    public const PORTS = [
        'LAGOS' => ['Nigeria', 'destination', ['LAGOS NIGERIA', 'LAG', 'LAGOS PORT']],
        'LAGOS (APAPA)' => ['Nigeria', 'destination', ['APAPA']],
        'LAGOS (TIN CAN)' => ['Nigeria', 'destination', ['TIN CAN', 'TINCAN', 'TIN CAN ISLAND']],
        'LAGOS (LEKKI)' => ['Nigeria', 'destination', ['LEKKI', 'LEKKI DEEP SEA']],
        'ABUJA' => ['Nigeria', 'destination', []],
        'PORT HARCOURT' => ['Nigeria', 'destination', ['PH', 'ONNE']],
        'COTONOU' => ['Benin', 'destination', []],
        'TEMA' => ['Ghana', 'destination', []],
        'MONTREAL' => ['Canada', 'origin', ['MONTREAL QC', 'MTL']],
        'HALIFAX' => ['Canada', 'origin', []],
        'TORONTO' => ['Canada', 'origin', ['TORONTO ON']],
        'VANCOUVER' => ['Canada', 'origin', []],
        'NEWARK' => ['USA', 'origin', ['NEW YORK', 'NY/NJ']],
        'BALTIMORE' => ['USA', 'origin', []],
        'SAVANNAH' => ['USA', 'origin', []],
        'HOUSTON' => ['USA', 'origin', []],
    ];

    public function run(): void
    {
        foreach (self::MAKES as $makeName => [$makeAliases, $models]) {
            $make = $this->upsert(VehicleMake::query(), ['name' => $makeName], $makeAliases);
            foreach ($models as $modelName => $modelAliases) {
                $this->upsert(VehicleModel::query(), ['vehicle_make_id' => $make->id, 'name' => $modelName], $modelAliases);
            }
        }

        foreach (self::SHIPPING_LINES as $name => $line) {
            $model = $this->upsert(ShippingLine::query(), ['name' => $name], $line['aliases'], [
                'scac' => $line['scac'],
                'tracking_url' => $line['tracking_url'],
            ]);
            $model->container_prefixes = array_values(array_unique(array_merge($model->container_prefixes ?? [], $line['prefixes'])));
            $model->save();
        }

        foreach (self::PLATFORMS as $name => $aliases) {
            $this->upsert(PurchasePlatform::query(), ['name' => $name], $aliases);
        }

        foreach (self::PORTS as $name => [$country, $kind, $aliases]) {
            $this->upsert(Port::query(), ['name' => $name], $aliases, ['country' => $country, 'kind' => $kind]);
        }
    }

    private function upsert($query, array $keys, array $aliases, array $extra = [])
    {
        $model = $query->firstOrNew($keys);
        foreach ($extra as $field => $value) {
            if ($model->{$field} === null) {
                $model->{$field} = $value;
            }
        }
        $model->aliases = array_values(array_unique(array_merge($model->aliases ?? [], $aliases)));
        $model->save();

        return $model;
    }
}
