import { Link } from '@inertiajs/react';
import { Menu, X, Truck, Phone, Mail, MapPin, Ship, Package, Car } from 'lucide-react';
import { useState } from 'react';

export default function WebsiteLayout({ children }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const navigation = [
        { name: 'Home', href: '/' },
        { name: 'Inventory', href: '/inventory' },
        { name: 'Ship a Car', href: '/ship' },
        { name: 'Contact', href: '/contact' },
    ];

    return (
        <div className="min-h-screen bg-white">
            {/* Header */}
            <header className="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100">
                <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16 md:h-20">
                        {/* Logo */}
                        <Link href="/" className="flex items-center gap-2">
                            <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center">
                                <Ship className="w-6 h-6 text-white" />
                            </div>
                            <span className="text-xl font-bold text-gray-900">ADGOD</span>
                        </Link>

                        {/* Desktop Navigation */}
                        <div className="hidden md:flex items-center gap-8">
                            {navigation.map((item) => (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className="text-gray-600 hover:text-blue-600 font-medium transition-colors"
                                >
                                    {item.name}
                                </Link>
                            ))}
                            <Link
                                href="/ship"
                                className="bg-blue-600 text-white px-6 py-2.5 rounded-full font-semibold hover:bg-blue-700 transition-colors"
                            >
                                Get Started
                            </Link>
                        </div>

                        {/* Mobile Menu Button */}
                        <button
                            className="md:hidden p-2 rounded-lg hover:bg-gray-100"
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        >
                            {mobileMenuOpen ? (
                                <X className="w-6 h-6 text-gray-600" />
                            ) : (
                                <Menu className="w-6 h-6 text-gray-600" />
                            )}
                        </button>
                    </div>

                    {/* Mobile Navigation */}
                    {mobileMenuOpen && (
                        <div className="md:hidden py-4 border-t border-gray-100">
                            <div className="flex flex-col gap-2">
                                {navigation.map((item) => (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className="px-4 py-3 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg font-medium transition-colors"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        {item.name}
                                    </Link>
                                ))}
                                <Link
                                    href="/ship"
                                    className="mx-4 mt-2 bg-blue-600 text-white px-6 py-3 rounded-full font-semibold text-center hover:bg-blue-700 transition-colors"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Get Started
                                </Link>
                            </div>
                        </div>
                    )}
                </nav>
            </header>

            {/* Main Content */}
            <main className="pt-16 md:pt-20">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-gray-900 text-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12">
                        {/* Brand */}
                        <div className="md:col-span-1">
                            <div className="flex items-center gap-2 mb-4">
                                <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center">
                                    <Ship className="w-6 h-6 text-white" />
                                </div>
                                <span className="text-xl font-bold">ADGOD</span>
                            </div>
                            <p className="text-gray-400 text-sm">
                                Professional vehicle shipping and logistics services. Your trusted partner for reliable auto transport.
                            </p>
                        </div>

                        {/* Quick Links */}
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wider mb-4">Quick Links</h3>
                            <ul className="space-y-3">
                                {navigation.map((item) => (
                                    <li key={item.name}>
                                        <Link href={item.href} className="text-gray-400 hover:text-white transition-colors">
                                            {item.name}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        {/* Services */}
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wider mb-4">Services</h3>
                            <ul className="space-y-3">
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Car className="w-4 h-4" /> Vehicle Shipping
                                </li>
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Package className="w-4 h-4" /> Container Shipping
                                </li>
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Ship className="w-4 h-4" /> RoRo Shipping
                                </li>
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Truck className="w-4 h-4" /> Tow Services
                                </li>
                            </ul>
                        </div>

                        {/* Contact */}
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wider mb-4">Contact Us</h3>
                            <ul className="space-y-3">
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Phone className="w-4 h-4" />
                                    <span>Call us today</span>
                                </li>
                                <li className="flex items-center gap-2 text-gray-400">
                                    <Mail className="w-4 h-4" />
                                    <span>info@adgod.com</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="mt-12 pt-8 border-t border-gray-800 text-center text-gray-400 text-sm">
                        <p>&copy; {new Date().getFullYear()} ADGOD Shipping & Logistics. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
