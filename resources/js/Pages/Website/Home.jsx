import { Head, Link } from '@inertiajs/react';
import WebsiteLayout from '@/Layouts/WebsiteLayout';
import { ArrowRight, Shield, Clock, Globe, Truck, Ship, Package, CheckCircle2, Star } from 'lucide-react';

export default function Home({ vehicleCount = 0 }) {
    const features = [
        {
            icon: Shield,
            title: 'Fully Insured',
            description: 'All shipments are fully insured for your peace of mind.'
        },
        {
            icon: Clock,
            title: 'Fast & Reliable',
            description: 'Timely deliveries with real-time tracking updates.'
        },
        {
            icon: Globe,
            title: 'Worldwide Shipping',
            description: 'We ship vehicles to destinations around the world.'
        },
    ];

    const services = [
        {
            icon: Ship,
            title: 'RoRo Shipping',
            description: 'Roll-on/Roll-off shipping for drivable vehicles. Cost-effective and efficient.',
            color: 'bg-blue-500'
        },
        {
            icon: Package,
            title: 'Container Shipping',
            description: 'Secure container shipping for maximum protection of your vehicle.',
            color: 'bg-green-500'
        },
        {
            icon: Truck,
            title: 'Tow Services',
            description: 'Door-to-door pickup and delivery with our trusted tow partners.',
            color: 'bg-orange-500'
        },
    ];

    const stats = [
        { value: '1000+', label: 'Vehicles Shipped' },
        { value: '50+', label: 'Countries' },
        { value: '98%', label: 'Satisfaction Rate' },
        { value: '24/7', label: 'Support' },
    ];

    return (
        <WebsiteLayout>
            <Head title="Home - Vehicle Shipping & Logistics" />

            {/* Hero Section */}
            <section className="relative bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900 text-white overflow-hidden">
                <div className="absolute inset-0 bg-[url('/images/hero-pattern.svg')] opacity-10"></div>
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900/50 to-transparent"></div>

                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 lg:py-40">
                    <div className="max-w-3xl">
                        <div className="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-500/30 rounded-full px-4 py-1.5 mb-6">
                            <Star className="w-4 h-4 text-yellow-400" />
                            <span className="text-sm font-medium text-blue-200">Trusted by hundreds of customers</span>
                        </div>

                        <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                            Ship Your Vehicle <br />
                            <span className="text-blue-400">Anywhere in the World</span>
                        </h1>

                        <p className="text-lg md:text-xl text-gray-300 mb-8 max-w-2xl">
                            Professional vehicle shipping services with competitive rates. From pickup to delivery, we handle everything with care.
                        </p>

                        <div className="flex flex-col sm:flex-row gap-4">
                            <Link
                                href="/ship"
                                className="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-700 transition-all hover:scale-105"
                            >
                                Start Shipping <ArrowRight className="w-5 h-5" />
                            </Link>
                            <Link
                                href="/inventory"
                                className="inline-flex items-center justify-center gap-2 bg-white/10 border border-white/20 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white/20 transition-all"
                            >
                                View Inventory
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Floating Stats */}
                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 md:pb-16">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        {stats.map((stat, index) => (
                            <div key={index} className="bg-white/10 backdrop-blur-sm rounded-2xl p-4 md:p-6 text-center border border-white/10">
                                <div className="text-2xl md:text-3xl font-bold text-white mb-1">{stat.value}</div>
                                <div className="text-sm text-gray-400">{stat.label}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="py-16 md:py-24 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-12 md:mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Why Choose Us?
                        </h2>
                        <p className="text-gray-600 max-w-2xl mx-auto">
                            We provide comprehensive vehicle shipping solutions with a focus on safety, reliability, and customer satisfaction.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {features.map((feature, index) => (
                            <div key={index} className="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow">
                                <div className="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                                    <feature.icon className="w-7 h-7 text-blue-600" />
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 mb-3">{feature.title}</h3>
                                <p className="text-gray-600">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Services Section */}
            <section className="py-16 md:py-24">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-12 md:mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Our Services
                        </h2>
                        <p className="text-gray-600 max-w-2xl mx-auto">
                            Choose the shipping method that best suits your needs and budget.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {services.map((service, index) => (
                            <div key={index} className="group relative bg-white rounded-2xl p-8 border border-gray-100 hover:border-blue-200 hover:shadow-xl transition-all">
                                <div className={`w-14 h-14 ${service.color} rounded-xl flex items-center justify-center mb-6`}>
                                    <service.icon className="w-7 h-7 text-white" />
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 mb-3">{service.title}</h3>
                                <p className="text-gray-600 mb-6">{service.description}</p>
                                <Link
                                    href="/ship"
                                    className="inline-flex items-center gap-2 text-blue-600 font-medium group-hover:gap-3 transition-all"
                                >
                                    Get Quote <ArrowRight className="w-4 h-4" />
                                </Link>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Inventory Preview */}
            {vehicleCount > 0 && (
                <section className="py-16 md:py-24 bg-gray-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Browse Our Inventory
                        </h2>
                        <p className="text-gray-600 mb-8 max-w-2xl mx-auto">
                            We have <span className="font-semibold text-blue-600">{vehicleCount} vehicles</span> currently in stock and ready for shipping.
                        </p>
                        <Link
                            href="/inventory"
                            className="inline-flex items-center gap-2 bg-blue-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-700 transition-all hover:scale-105"
                        >
                            View All Vehicles <ArrowRight className="w-5 h-5" />
                        </Link>
                    </div>
                </section>
            )}

            {/* CTA Section */}
            <section className="py-16 md:py-24 bg-gradient-to-br from-blue-600 to-blue-800 text-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl md:text-4xl font-bold mb-4">
                        Ready to Ship Your Vehicle?
                    </h2>
                    <p className="text-blue-100 mb-8 max-w-2xl mx-auto text-lg">
                        Submit your shipment request today and get a quote within 24 hours. Our team is ready to assist you.
                    </p>
                    <div className="flex flex-col sm:flex-row justify-center gap-4">
                        <Link
                            href="/ship"
                            className="inline-flex items-center justify-center gap-2 bg-white text-blue-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all hover:scale-105"
                        >
                            Submit Request <ArrowRight className="w-5 h-5" />
                        </Link>
                        <Link
                            href="/contact"
                            className="inline-flex items-center justify-center gap-2 bg-blue-500/30 border border-white/30 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-500/50 transition-all"
                        >
                            Contact Us
                        </Link>
                    </div>
                </div>
            </section>

            {/* Trust Indicators */}
            <section className="py-12 md:py-16 border-t border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap justify-center items-center gap-8 md:gap-16">
                        <div className="flex items-center gap-2 text-gray-500">
                            <CheckCircle2 className="w-5 h-5 text-green-500" />
                            <span>Licensed & Bonded</span>
                        </div>
                        <div className="flex items-center gap-2 text-gray-500">
                            <CheckCircle2 className="w-5 h-5 text-green-500" />
                            <span>Fully Insured</span>
                        </div>
                        <div className="flex items-center gap-2 text-gray-500">
                            <CheckCircle2 className="w-5 h-5 text-green-500" />
                            <span>Door-to-Door Service</span>
                        </div>
                        <div className="flex items-center gap-2 text-gray-500">
                            <CheckCircle2 className="w-5 h-5 text-green-500" />
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
