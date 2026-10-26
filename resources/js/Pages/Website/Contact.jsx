import { Head, useForm } from '@inertiajs/react';
import WebsiteLayout from '@/Layouts/WebsiteLayout';
import { Phone, Mail, MapPin, Clock, Send, CheckCircle, AlertCircle } from 'lucide-react';
import { useState, useEffect } from 'react';

export default function Contact({ flash }) {
    const [submitted, setSubmitted] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        service_type: '',
        message: '',
    });

    useEffect(() => {
        if (flash?.success) {
            setSubmitted(true);
            reset();
        }
    }, [flash]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/enquiry', {
            onSuccess: () => {
                setSubmitted(true);
            },
        });
    };

    const services = [
        { value: '', label: 'Select a service' },
        { value: 'vehicle_shipping', label: 'Vehicle Shipping' },
        { value: 'container_shipping', label: 'Container Shipping' },
        { value: 'roro_shipping', label: 'RoRo Shipping' },
        { value: 'tow_services', label: 'Tow Services' },
        { value: 'vehicle_purchase', label: 'Vehicle Purchase' },
        { value: 'general', label: 'General Inquiry' },
    ];

    const contactInfo = [
        {
            icon: Phone,
            title: 'Phone',
            content: 'Call us anytime',
            action: 'tel:+1234567890',
            actionText: '+1 (234) 567-890',
        },
        {
            icon: Mail,
            title: 'Email',
            content: 'Send us an email',
            action: 'mailto:info@adgod.com',
            actionText: 'info@adgod.com',
        },
        {
            icon: Clock,
            title: 'Business Hours',
            content: 'Mon - Fri: 9AM - 6PM',
            actionText: 'Sat: 10AM - 4PM',
        },
    ];

    return (
        <WebsiteLayout>
            <Head title="Contact Us" />

            {/* Hero Section */}
            <section className="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16 md:py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                            Contact Us
                        </h1>
                        <p className="text-gray-300 text-lg max-w-2xl mx-auto">
                            Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                        </p>
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section className="py-12 md:py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                        {/* Contact Info Cards */}
                        <div className="lg:col-span-1 space-y-6">
                            {contactInfo.map((item, index) => (
                                <div key={index} className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                                    <div className="flex items-start gap-4">
                                        <div className="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <item.icon className="w-6 h-6 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900 mb-1">{item.title}</h3>
                                            <p className="text-gray-500 text-sm mb-2">{item.content}</p>
                                            {item.action ? (
                                                <a href={item.action} className="text-blue-600 font-medium hover:text-blue-700">
                                                    {item.actionText}
                                                </a>
                                            ) : (
                                                <span className="text-gray-700">{item.actionText}</span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}

                            {/* Quick Message */}
                            <div className="bg-blue-50 rounded-2xl p-6 border border-blue-100">
                                <h3 className="font-semibold text-gray-900 mb-2">Quick Response</h3>
                                <p className="text-gray-600 text-sm">
                                    We typically respond to inquiries within 24 hours during business days. For urgent matters, please call us directly.
                                </p>
                            </div>
                        </div>

                        {/* Contact Form */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100">
                                {submitted ? (
                                    <div className="text-center py-12">
                                        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                            <CheckCircle className="w-8 h-8 text-green-600" />
                                        </div>
                                        <h2 className="text-2xl font-bold text-gray-900 mb-2">Thank You!</h2>
                                        <p className="text-gray-600 mb-6">
                                            Your message has been sent successfully. We'll get back to you soon.
                                        </p>
                                        <button
                                            onClick={() => setSubmitted(false)}
                                            className="text-blue-600 font-medium hover:text-blue-700"
                                        >
                                            Send another message
                                        </button>
                                    </div>
                                ) : (
                                    <>
                                        <h2 className="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                                        <form onSubmit={handleSubmit} className="space-y-6">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {/* Name */}
                                                <div>
                                                    <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Full Name <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="name"
                                                        value={data.name}
                                                        onChange={(e) => setData('name', e.target.value)}
                                                        className={`w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.name ? 'border-red-500' : 'border-gray-200'}`}
                                                        placeholder="John Doe"
                                                    />
                                                    {errors.name && (
                                                        <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                                            <AlertCircle className="w-4 h-4" /> {errors.name}
                                                        </p>
                                                    )}
                                                </div>

                                                {/* Email */}
                                                <div>
                                                    <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Email Address <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="email"
                                                        id="email"
                                                        value={data.email}
                                                        onChange={(e) => setData('email', e.target.value)}
                                                        className={`w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.email ? 'border-red-500' : 'border-gray-200'}`}
                                                        placeholder="john@example.com"
                                                    />
                                                    {errors.email && (
                                                        <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                                            <AlertCircle className="w-4 h-4" /> {errors.email}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {/* Phone */}
                                                <div>
                                                    <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Phone Number
                                                    </label>
                                                    <input
                                                        type="tel"
                                                        id="phone"
                                                        value={data.phone}
                                                        onChange={(e) => setData('phone', e.target.value)}
                                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        placeholder="+1 (234) 567-890"
                                                    />
                                                </div>

                                                {/* Service Type */}
                                                <div>
                                                    <label htmlFor="service_type" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Service Interest
                                                    </label>
                                                    <select
                                                        id="service_type"
                                                        value={data.service_type}
                                                        onChange={(e) => setData('service_type', e.target.value)}
                                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                    >
                                                        {services.map((service) => (
                                                            <option key={service.value} value={service.value}>
                                                                {service.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                            </div>

                                            {/* Message */}
                                            <div>
                                                <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-2">
                                                    Message <span className="text-red-500">*</span>
                                                </label>
                                                <textarea
                                                    id="message"
                                                    rows={5}
                                                    value={data.message}
                                                    onChange={(e) => setData('message', e.target.value)}
                                                    className={`w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none ${errors.message ? 'border-red-500' : 'border-gray-200'}`}
                                                    placeholder="Tell us about your shipping needs..."
                                                />
                                                {errors.message && (
                                                    <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                                        <AlertCircle className="w-4 h-4" /> {errors.message}
                                                    </p>
                                                )}
                                            </div>

                                            {/* Submit Button */}
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="w-full md:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {processing ? (
                                                    <>
                                                        <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                                        Sending...
                                                    </>
                                                ) : (
                                                    <>
                                                        <Send className="w-5 h-5" />
                                                        Send Message
                                                    </>
                                                )}
                                            </button>
                                        </form>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
