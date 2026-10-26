import { Head, Link, router } from '@inertiajs/react';
import WebsiteLayout from '@/Layouts/WebsiteLayout';
import { Search, Filter, Car, Calendar, Palette, ChevronLeft, ChevronRight, X, Tag } from 'lucide-react';
import { useState } from 'react';

export default function Inventory({ vehicles, makes, years, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [selectedMake, setSelectedMake] = useState(filters?.make || '');
    const [selectedYear, setSelectedYear] = useState(filters?.year || '');
    const [selectedCondition, setSelectedCondition] = useState(filters?.condition || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e) => {
        e.preventDefault();
        applyFilters();
    };

    const applyFilters = () => {
        router.get('/inventory', {
            search: search || undefined,
            make: selectedMake || undefined,
            year: selectedYear || undefined,
            condition: selectedCondition || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedMake('');
        setSelectedYear('');
        setSelectedCondition('');
        router.get('/inventory');
    };

    const hasFilters = search || selectedMake || selectedYear || selectedCondition;

    const conditions = [
        { value: '', label: 'All Conditions' },
        { value: 'new', label: 'New' },
        { value: 'used', label: 'Used' },
        { value: 'salvage', label: 'Salvage' },
    ];

    const getConditionColor = (condition) => {
        switch (condition) {
            case 'new': return 'bg-green-100 text-green-700';
            case 'used': return 'bg-blue-100 text-blue-700';
            case 'salvage': return 'bg-orange-100 text-orange-700';
            default: return 'bg-gray-100 text-gray-700';
        }
    };

    return (
        <WebsiteLayout>
            <Head title="Inventory - Available Vehicles" />

            {/* Hero Section */}
            <section className="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16 md:py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                            Our Vehicle Inventory
                        </h1>
                        <p className="text-gray-300 text-lg max-w-2xl mx-auto">
                            Browse our selection of vehicles available for purchase and shipping.
                        </p>
                    </div>
                </div>
            </section>

            {/* Search and Filters */}
            <section className="bg-white border-b border-gray-200 sticky top-16 md:top-20 z-40">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="flex gap-2 mb-4">
                        <div className="relative flex-1">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search by make, model, VIN, or year..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            type="submit"
                            className="bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition-colors"
                        >
                            Search
                        </button>
                        <button
                            type="button"
                            onClick={() => setShowFilters(!showFilters)}
                            className="md:hidden bg-gray-100 text-gray-700 px-4 py-3 rounded-xl font-medium hover:bg-gray-200 transition-colors"
                        >
                            <Filter className="w-5 h-5" />
                        </button>
                    </form>

                    {/* Desktop Filters */}
                    <div className={`${showFilters ? 'block' : 'hidden'} md:flex flex-wrap gap-3 items-center`}>
                        <select
                            value={selectedMake}
                            onChange={(e) => {
                                setSelectedMake(e.target.value);
                                setTimeout(applyFilters, 0);
                            }}
                            className="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            <option value="">All Makes</option>
                            {makes.map((make) => (
                                <option key={make} value={make}>{make}</option>
                            ))}
                        </select>

                        <select
                            value={selectedYear}
                            onChange={(e) => {
                                setSelectedYear(e.target.value);
                                setTimeout(applyFilters, 0);
                            }}
                            className="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            <option value="">All Years</option>
                            {years.map((year) => (
                                <option key={year} value={year}>{year}</option>
                            ))}
                        </select>

                        <select
                            value={selectedCondition}
                            onChange={(e) => {
                                setSelectedCondition(e.target.value);
                                setTimeout(applyFilters, 0);
                            }}
                            className="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            {conditions.map((condition) => (
                                <option key={condition.value} value={condition.value}>{condition.label}</option>
                            ))}
                        </select>

                        {hasFilters && (
                            <button
                                onClick={clearFilters}
                                className="flex items-center gap-1 text-gray-500 hover:text-gray-700 px-3 py-2"
                            >
                                <X className="w-4 h-4" /> Clear Filters
                            </button>
                        )}

                        <div className="ml-auto text-sm text-gray-500">
                            {vehicles.total} vehicle{vehicles.total !== 1 ? 's' : ''} found
                        </div>
                    </div>
                </div>
            </section>

            {/* Vehicle Grid */}
            <section className="py-8 md:py-12 bg-gray-50 min-h-[50vh]">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {vehicles.data.length > 0 ? (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                {vehicles.data.map((vehicle) => (
                                    <VehicleCard key={vehicle.id} vehicle={vehicle} getConditionColor={getConditionColor} />
                                ))}
                            </div>

                            {/* Pagination */}
                            {vehicles.last_page > 1 && (
                                <div className="mt-8 flex justify-center">
                                    <nav className="flex items-center gap-2">
                                        {vehicles.prev_page_url && (
                                            <Link
                                                href={vehicles.prev_page_url}
                                                className="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                                            >
                                                <ChevronLeft className="w-4 h-4" /> Previous
                                            </Link>
                                        )}

                                        <span className="px-4 py-2 text-gray-600">
                                            Page {vehicles.current_page} of {vehicles.last_page}
                                        </span>

                                        {vehicles.next_page_url && (
                                            <Link
                                                href={vehicles.next_page_url}
                                                className="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                                            >
                                                Next <ChevronRight className="w-4 h-4" />
                                            </Link>
                                        )}
                                    </nav>
                                </div>
                            )}
                        </>
                    ) : (
                        <div className="text-center py-16">
                            <Car className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <h3 className="text-xl font-semibold text-gray-900 mb-2">No Vehicles Found</h3>
                            <p className="text-gray-500 mb-6">
                                {hasFilters
                                    ? "Try adjusting your filters or search terms."
                                    : "Check back later for new inventory."}
                            </p>
                            {hasFilters && (
                                <button
                                    onClick={clearFilters}
                                    className="text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    Clear all filters
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </section>

            {/* CTA */}
            <section className="py-12 md:py-16 bg-white border-t border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                        Need to Ship a Vehicle?
                    </h2>
                    <p className="text-gray-600 mb-6 max-w-2xl mx-auto">
                        Whether you're buying from our inventory or shipping your own vehicle, we've got you covered.
                    </p>
                    <Link
                        href="/ship"
                        className="inline-flex items-center gap-2 bg-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-blue-700 transition-colors"
                    >
                        Submit Shipment Request
                    </Link>
                </div>
            </section>
        </WebsiteLayout>
    );
}

function VehicleCard({ vehicle, getConditionColor }) {
    // Get first image from media or use placeholder
    const imageUrl = vehicle.media && vehicle.media.length > 0
        ? `/storage/${vehicle.media[0].id}/${vehicle.media[0].file_name}`
        : null;

    return (
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all group">
            {/* Image */}
            <div className="aspect-[4/3] bg-gray-100 relative overflow-hidden">
                {imageUrl ? (
                    <img
                        src={imageUrl}
                        alt={`${vehicle.year} ${vehicle.make} ${vehicle.model}`}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center">
                        <Car className="w-16 h-16 text-gray-300" />
                    </div>
                )}

                {/* Condition Badge */}
                <div className={`absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-medium ${getConditionColor(vehicle.condition)}`}>
                    {vehicle.condition ? vehicle.condition.charAt(0).toUpperCase() + vehicle.condition.slice(1) : 'Used'}
                </div>
            </div>

            {/* Content */}
            <div className="p-4">
                <h3 className="text-lg font-semibold text-gray-900 mb-1">
                    {vehicle.year} {vehicle.make} {vehicle.model}
                </h3>

                <div className="flex flex-wrap gap-2 mb-3">
                    {vehicle.color && (
                        <span className="inline-flex items-center gap-1 text-xs text-gray-500">
                            <Palette className="w-3 h-3" /> {vehicle.color}
                        </span>
                    )}
                    {vehicle.vin && (
                        <span className="inline-flex items-center gap-1 text-xs text-gray-500">
                            <Tag className="w-3 h-3" /> {vehicle.vin.slice(-6)}
                        </span>
                    )}
                </div>

                {vehicle.sale_price && (
                    <div className="text-xl font-bold text-blue-600">
                        ${parseFloat(vehicle.sale_price).toLocaleString()}
                    </div>
                )}

                <Link
                    href="/contact"
                    className="mt-4 block w-full text-center bg-gray-100 text-gray-700 py-2.5 rounded-lg font-medium hover:bg-blue-600 hover:text-white transition-colors"
                >
                    Inquire
                </Link>
            </div>
        </div>
    );
}
