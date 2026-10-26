import { Head, useForm } from '@inertiajs/react';
import WebsiteLayout from '@/Layouts/WebsiteLayout';
import { useState, useEffect, useRef } from 'react';
import {
    Ship, Package, HelpCircle, Wrench, Truck, Check, ChevronRight, ChevronLeft,
    User, Phone, Car, Palette, Hash, FileText, Upload, X, AlertCircle, CheckCircle2,
    Loader2
} from 'lucide-react';

export default function ShipmentRequest({ flash }) {
    const [currentStep, setCurrentStep] = useState(1);
    const [submitted, setSubmitted] = useState(false);
    const [uploadedFiles, setUploadedFiles] = useState([]);
    const fileInputRef = useRef(null);

    const totalSteps = 6;

    const { data, setData, post, processing, errors, reset, progress } = useForm({
        shipper_name: '',
        phone_number: '',
        car_make_model: '',
        colour: '',
        vin: '',
        vehicle_year: '',
        mode_of_shipping: '',
        ready_to_go: '',
        if_no_why: '',
        ownership: '',
        ownership_location: '',
        extra_notes: '',
        tow_truck: '',
        driver_name: '',
        documents: [],
    });

    useEffect(() => {
        if (flash?.success) {
            setSubmitted(true);
        }
    }, [flash]);

    const handleFileUpload = (e) => {
        const files = Array.from(e.target.files);
        const newFiles = [...uploadedFiles, ...files];
        setUploadedFiles(newFiles);
        setData('documents', newFiles);
    };

    const removeFile = (index) => {
        const newFiles = uploadedFiles.filter((_, i) => i !== index);
        setUploadedFiles(newFiles);
        setData('documents', newFiles);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const formData = new FormData();

        Object.keys(data).forEach(key => {
            if (key === 'documents') {
                uploadedFiles.forEach((file, index) => {
                    formData.append(`documents[${index}]`, file);
                });
            } else {
                formData.append(key, data[key]);
            }
        });

        post('/ship', {
            forceFormData: true,
            onSuccess: () => {
                setSubmitted(true);
            },
        });
    };

    const nextStep = () => {
        if (currentStep < totalSteps) {
            setCurrentStep(currentStep + 1);
        }
    };

    const prevStep = () => {
        if (currentStep > 1) {
            setCurrentStep(currentStep - 1);
        }
    };

    const canProceed = () => {
        switch (currentStep) {
            case 1:
                return data.shipper_name && data.phone_number;
            case 2:
                return data.car_make_model;
            case 3:
                return data.mode_of_shipping;
            case 4:
                return data.ready_to_go;
            case 5:
                return data.ownership;
            case 6:
                return data.tow_truck;
            default:
                return true;
        }
    };

    const shippingModes = [
        { value: 'ADGOD', label: 'ADGOD', icon: Ship, description: 'Our standard shipping service' },
        { value: 'CONTAINER', label: 'Container', icon: Package, description: 'Secure container shipping' },
        { value: 'RORO', label: 'RoRo', icon: Ship, description: 'Roll-on/Roll-off shipping' },
        { value: 'UNSURE', label: 'Not Sure', icon: HelpCircle, description: 'We\'ll help you decide' },
        { value: 'REPAIR', label: 'Repair', icon: Wrench, description: 'Vehicle needs repairs' },
    ];

    const readyOptions = [
        { value: 'YES', label: 'Yes, Ready', color: 'bg-green-500', hoverColor: 'hover:bg-green-600' },
        { value: 'NO', label: 'No, Not Ready', color: 'bg-orange-500', hoverColor: 'hover:bg-orange-600' },
        { value: 'NA', label: 'N/A', color: 'bg-gray-500', hoverColor: 'hover:bg-gray-600' },
    ];

    const ownershipOptions = [
        { value: 'YES', label: 'Yes, I have title', description: 'I have the ownership documents' },
        { value: 'NO', label: 'No', description: 'I don\'t have ownership yet' },
        { value: 'BILL_OF_SALES', label: 'Bill of Sale', description: 'I have a bill of sale' },
    ];

    const locationOptions = [
        { value: 'WASSAP', label: 'WhatsApp', description: 'Send via WhatsApp' },
        { value: 'FILE', label: 'File Upload', description: 'Upload documents now' },
        { value: 'DRIVE', label: 'Google Drive', description: 'Share via Drive' },
    ];

    const towOptions = [
        { value: 'YES', label: 'Yes, Need Tow', icon: Truck, color: 'bg-blue-500' },
        { value: 'NO', label: 'No, Don\'t Need', icon: Car, color: 'bg-green-500' },
        { value: 'UNSURE_NA', label: 'Unsure / N/A', icon: HelpCircle, color: 'bg-gray-500' },
    ];

    const driverOptions = [
        { value: '', label: 'Not Specified' },
        { value: 'SILVA', label: 'Silva' },
        { value: 'PASTOR SUNDAY', label: 'Pastor Sunday' },
        { value: 'GLOBE', label: 'Globe' },
        { value: 'SAM - 647-770-2851', label: 'Sam (647-770-2851)' },
        { value: 'OSEHI', label: 'Osehi' },
        { value: 'OLA', label: 'Ola' },
    ];

    if (submitted) {
        return (
            <WebsiteLayout>
                <Head title="Request Submitted" />
                <div className="min-h-[70vh] flex items-center justify-center px-4">
                    <div className="max-w-md w-full text-center">
                        <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <CheckCircle2 className="w-10 h-10 text-green-600" />
                        </div>
                        <h1 className="text-3xl font-bold text-gray-900 mb-4">Request Submitted!</h1>
                        <p className="text-gray-600 mb-8">
                            Thank you for your shipment request. Our team will review it and contact you within 24-48 hours.
                        </p>
                        <div className="space-y-3">
                            <a
                                href="/"
                                className="block w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors"
                            >
                                Back to Home
                            </a>
                            <button
                                onClick={() => {
                                    setSubmitted(false);
                                    setCurrentStep(1);
                                    reset();
                                    setUploadedFiles([]);
                                }}
                                className="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-colors"
                            >
                                Submit Another Request
                            </button>
                        </div>
                    </div>
                </div>
            </WebsiteLayout>
        );
    }

    return (
        <WebsiteLayout>
            <Head title="Ship a Car - Request Form" />

            {/* Progress Bar */}
            <div className="bg-white border-b border-gray-100 sticky top-16 md:top-20 z-40">
                <div className="max-w-3xl mx-auto px-4 py-4">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-gray-600">Step {currentStep} of {totalSteps}</span>
                        <span className="text-sm text-gray-500">{Math.round((currentStep / totalSteps) * 100)}% Complete</span>
                    </div>
                    <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500 ease-out"
                            style={{ width: `${(currentStep / totalSteps) * 100}%` }}
                        />
                    </div>
                </div>
            </div>

            {/* Form Container */}
            <div className="min-h-[70vh] flex items-center justify-center py-8 md:py-12 px-4 bg-gray-50">
                <div className="w-full max-w-2xl">
                    <form onSubmit={handleSubmit}>
                        {/* Step 1: Contact Info */}
                        {currentStep === 1 && (
                            <StepContainer
                                title="Let's start with your info"
                                subtitle="We'll use this to contact you about your shipment"
                            >
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Your Name <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <User className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input
                                                type="text"
                                                value={data.shipper_name}
                                                onChange={(e) => setData('shipper_name', e.target.value)}
                                                className="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                                                placeholder="Enter your full name"
                                                autoFocus
                                            />
                                        </div>
                                        {errors.shipper_name && <ErrorMessage message={errors.shipper_name} />}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Phone Number <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <Phone className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input
                                                type="tel"
                                                value={data.phone_number}
                                                onChange={(e) => setData('phone_number', e.target.value)}
                                                className="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                                                placeholder="(123) 456-7890"
                                            />
                                        </div>
                                        {errors.phone_number && <ErrorMessage message={errors.phone_number} />}
                                    </div>
                                </div>
                            </StepContainer>
                        )}

                        {/* Step 2: Vehicle Info */}
                        {currentStep === 2 && (
                            <StepContainer
                                title="Tell us about your vehicle"
                                subtitle="Provide details about the car you want to ship"
                            >
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Car Make & Model <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <Car className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input
                                                type="text"
                                                value={data.car_make_model}
                                                onChange={(e) => setData('car_make_model', e.target.value)}
                                                className="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                                                placeholder="e.g. Toyota Camry"
                                                autoFocus
                                            />
                                        </div>
                                        {errors.car_make_model && <ErrorMessage message={errors.car_make_model} />}
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Year
                                            </label>
                                            <input
                                                type="text"
                                                value={data.vehicle_year}
                                                onChange={(e) => setData('vehicle_year', e.target.value)}
                                                className="w-full px-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                                                placeholder="2024"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Colour
                                            </label>
                                            <div className="relative">
                                                <Palette className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                                <input
                                                    type="text"
                                                    value={data.colour}
                                                    onChange={(e) => setData('colour', e.target.value)}
                                                    className="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                                                    placeholder="Black"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            VIN Number
                                        </label>
                                        <div className="relative">
                                            <Hash className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input
                                                type="text"
                                                value={data.vin}
                                                onChange={(e) => setData('vin', e.target.value.toUpperCase())}
                                                className="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-mono"
                                                placeholder="1HGCM82633A004352"
                                                maxLength={17}
                                            />
                                        </div>
                                        <p className="mt-1 text-sm text-gray-500">17-character Vehicle Identification Number</p>
                                    </div>
                                </div>
                            </StepContainer>
                        )}

                        {/* Step 3: Shipping Mode */}
                        {currentStep === 3 && (
                            <StepContainer
                                title="How would you like to ship?"
                                subtitle="Select your preferred shipping method"
                            >
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {shippingModes.map((mode) => (
                                        <OptionCard
                                            key={mode.value}
                                            selected={data.mode_of_shipping === mode.value}
                                            onClick={() => setData('mode_of_shipping', mode.value)}
                                            icon={mode.icon}
                                            label={mode.label}
                                            description={mode.description}
                                        />
                                    ))}
                                </div>
                                {errors.mode_of_shipping && <ErrorMessage message={errors.mode_of_shipping} />}
                            </StepContainer>
                        )}

                        {/* Step 4: Ready Status */}
                        {currentStep === 4 && (
                            <StepContainer
                                title="Is the vehicle ready to go?"
                                subtitle="Let us know the current status"
                            >
                                <div className="grid grid-cols-3 gap-3 mb-6">
                                    {readyOptions.map((option) => (
                                        <button
                                            key={option.value}
                                            type="button"
                                            onClick={() => setData('ready_to_go', option.value)}
                                            className={`py-4 px-4 rounded-xl font-semibold text-white transition-all transform hover:scale-105 ${option.color} ${option.hoverColor} ${data.ready_to_go === option.value ? 'ring-4 ring-offset-2 ring-blue-500 scale-105' : 'opacity-80'
                                                }`}
                                        >
                                            {option.label}
                                        </button>
                                    ))}
                                </div>

                                {data.ready_to_go === 'NO' && (
                                    <div className="animate-fadeIn">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Why is it not ready?
                                        </label>
                                        <textarea
                                            value={data.if_no_why}
                                            onChange={(e) => setData('if_no_why', e.target.value)}
                                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            rows={3}
                                            placeholder="Please explain..."
                                            autoFocus
                                        />
                                    </div>
                                )}
                                {errors.ready_to_go && <ErrorMessage message={errors.ready_to_go} />}
                            </StepContainer>
                        )}

                        {/* Step 5: Ownership */}
                        {currentStep === 5 && (
                            <StepContainer
                                title="Do you have ownership documents?"
                                subtitle="Title, bill of sale, or registration"
                            >
                                <div className="space-y-3 mb-6">
                                    {ownershipOptions.map((option) => (
                                        <OptionCard
                                            key={option.value}
                                            selected={data.ownership === option.value}
                                            onClick={() => setData('ownership', option.value)}
                                            label={option.label}
                                            description={option.description}
                                            horizontal
                                        />
                                    ))}
                                </div>

                                {(data.ownership === 'YES' || data.ownership === 'BILL_OF_SALES') && (
                                    <div className="animate-fadeIn space-y-4">
                                        <label className="block text-sm font-medium text-gray-700">
                                            Where will you send the documents?
                                        </label>
                                        <div className="grid grid-cols-3 gap-3">
                                            {locationOptions.map((option) => (
                                                <button
                                                    key={option.value}
                                                    type="button"
                                                    onClick={() => setData('ownership_location', option.value)}
                                                    className={`py-3 px-3 rounded-xl border-2 transition-all text-center ${data.ownership_location === option.value
                                                            ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                            : 'border-gray-200 hover:border-gray-300'
                                                        }`}
                                                >
                                                    <div className="font-medium text-sm">{option.label}</div>
                                                </button>
                                            ))}
                                        </div>

                                        {data.ownership_location === 'FILE' && (
                                            <div className="animate-fadeIn">
                                                <div
                                                    onClick={() => fileInputRef.current?.click()}
                                                    className="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors"
                                                >
                                                    <Upload className="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                                    <p className="text-gray-600 font-medium">Click to upload documents</p>
                                                    <p className="text-sm text-gray-500 mt-1">PDF, JPG, PNG, DOC (max 10MB each)</p>
                                                </div>
                                                <input
                                                    ref={fileInputRef}
                                                    type="file"
                                                    multiple
                                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                    onChange={handleFileUpload}
                                                    className="hidden"
                                                />

                                                {uploadedFiles.length > 0 && (
                                                    <div className="mt-4 space-y-2">
                                                        {uploadedFiles.map((file, index) => (
                                                            <div key={index} className="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                                                                <div className="flex items-center gap-2">
                                                                    <FileText className="w-4 h-4 text-gray-500" />
                                                                    <span className="text-sm text-gray-700 truncate max-w-[200px]">{file.name}</span>
                                                                    <span className="text-xs text-gray-400">({(file.size / 1024).toFixed(0)} KB)</span>
                                                                </div>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => removeFile(index)}
                                                                    className="p-1 hover:bg-gray-200 rounded-full"
                                                                >
                                                                    <X className="w-4 h-4 text-gray-500" />
                                                                </button>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                )}
                                {errors.ownership && <ErrorMessage message={errors.ownership} />}
                            </StepContainer>
                        )}

                        {/* Step 6: Tow & Final */}
                        {currentStep === 6 && (
                            <StepContainer
                                title="Final Details"
                                subtitle="Almost done! Just a few more questions"
                            >
                                <div className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-3">
                                            Do you need a tow truck? <span className="text-red-500">*</span>
                                        </label>
                                        <div className="grid grid-cols-3 gap-3">
                                            {towOptions.map((option) => (
                                                <button
                                                    key={option.value}
                                                    type="button"
                                                    onClick={() => setData('tow_truck', option.value)}
                                                    className={`py-4 rounded-xl font-medium transition-all flex flex-col items-center gap-2 ${data.tow_truck === option.value
                                                            ? `${option.color} text-white ring-4 ring-offset-2 ring-blue-500`
                                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                                        }`}
                                                >
                                                    <option.icon className="w-6 h-6" />
                                                    <span className="text-sm">{option.label}</span>
                                                </button>
                                            ))}
                                        </div>
                                        {errors.tow_truck && <ErrorMessage message={errors.tow_truck} />}
                                    </div>

                                    {data.tow_truck === 'YES' && (
                                        <div className="animate-fadeIn">
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Preferred Driver (Optional)
                                            </label>
                                            <select
                                                value={data.driver_name}
                                                onChange={(e) => setData('driver_name', e.target.value)}
                                                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            >
                                                {driverOptions.map((driver) => (
                                                    <option key={driver.value} value={driver.value}>
                                                        {driver.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    )}

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Extra Notes (Optional)
                                        </label>
                                        <textarea
                                            value={data.extra_notes}
                                            onChange={(e) => setData('extra_notes', e.target.value)}
                                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            rows={4}
                                            placeholder="Any additional information we should know..."
                                        />
                                    </div>
                                </div>
                            </StepContainer>
                        )}

                        {/* Navigation Buttons */}
                        <div className="flex justify-between mt-8">
                            {currentStep > 1 ? (
                                <button
                                    type="button"
                                    onClick={prevStep}
                                    className="flex items-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition-colors"
                                >
                                    <ChevronLeft className="w-5 h-5" /> Back
                                </button>
                            ) : (
                                <div />
                            )}

                            {currentStep < totalSteps ? (
                                <button
                                    type="button"
                                    onClick={nextStep}
                                    disabled={!canProceed()}
                                    className="flex items-center gap-2 bg-blue-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Continue <ChevronRight className="w-5 h-5" />
                                </button>
                            ) : (
                                <button
                                    type="submit"
                                    disabled={processing || !canProceed()}
                                    className="flex items-center gap-2 bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {processing ? (
                                        <>
                                            <Loader2 className="w-5 h-5 animate-spin" /> Submitting...
                                        </>
                                    ) : (
                                        <>
                                            <Check className="w-5 h-5" /> Submit Request
                                        </>
                                    )}
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div>
        </WebsiteLayout>
    );
}

function StepContainer({ title, subtitle, children }) {
    return (
        <div className="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 animate-fadeIn">
            <div className="text-center mb-8">
                <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{title}</h2>
                <p className="text-gray-500">{subtitle}</p>
            </div>
            {children}
        </div>
    );
}

function OptionCard({ selected, onClick, icon: Icon, label, description, horizontal = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`w-full p-4 rounded-xl border-2 transition-all text-left ${horizontal ? 'flex items-center gap-4' : ''
                } ${selected
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                }`}
        >
            {Icon && (
                <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${selected ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500'
                    } ${horizontal ? 'flex-shrink-0' : 'mx-auto mb-3'}`}>
                    <Icon className="w-6 h-6" />
                </div>
            )}
            <div className={horizontal ? '' : 'text-center'}>
                <div className={`font-semibold ${selected ? 'text-blue-700' : 'text-gray-900'}`}>
                    {label}
                </div>
                {description && (
                    <div className="text-sm text-gray-500 mt-1">{description}</div>
                )}
            </div>
            {selected && (
                <div className={`${horizontal ? 'ml-auto' : 'absolute top-3 right-3'}`}>
                    <Check className="w-5 h-5 text-blue-500" />
                </div>
            )}
        </button>
    );
}

function ErrorMessage({ message }) {
    return (
        <p className="mt-2 text-sm text-red-500 flex items-center gap-1">
            <AlertCircle className="w-4 h-4" /> {message}
        </p>
    );
}
