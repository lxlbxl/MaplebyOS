import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card } from '@/Components/ui/card';
import {
    Settings as SettingsIcon,
    Mail,
    CreditCard,
    Shield,
    Globe,
    ChevronRight,
    Save,
    Zap,
    Lock,
    CheckCircle2
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { cn } from '@/lib/utils';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

export default function Index({ settingsData }) {
    const [activeTab, setActiveTab] = useState('general');
    const [testEmail, setTestEmail] = useState(settingsData?.general?.support_email || '');
    const [testingSmtp, setTestingSmtp] = useState(false);
    const { data, setData, post, processing } = useForm({
        settings: {
            ...(settingsData?.general || {}),
            ...(settingsData?.email || {}),
            ...(settingsData?.finance || {}),
            ...(settingsData?.security || {}),
        },
    });

    const handleTestSmtp = async () => {
        setTestingSmtp(true);
        try {
            const response = await fetch(route('settings.test-smtp'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    email: testEmail,
                    host: data.settings.smtp_host,
                    port: data.settings.smtp_port,
                    encryption: data.settings.smtp_encryption,
                    username: data.settings.smtp_username,
                    password: data.settings.smtp_password,
                }),
            });
            const result = await response.json();
            if (response.ok && result.success) {
                alert(result.message);
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            alert('Network Error: ' + error.message);
        } finally {
            setTestingSmtp(false);
        }
    };

    const categories = [
        { id: 'general', label: 'General', icon: Globe, description: 'Application naming & core settings' },
        { id: 'email', label: 'Email', icon: Mail, description: 'SMTP & notifications' },
        { id: 'finance', label: 'Finance', icon: CreditCard, description: 'Tax rates & currency' },
        { id: 'security', label: 'Security', icon: Shield, description: 'Access & protection' },
    ];

    const submit = (e) => {
        e.preventDefault();
        post(route('settings.update'), {
            preserveScroll: true,
        });
    };

    const updateSetting = (key, value) => {
        setData('settings', {
            ...data.settings,
            [key]: value,
        });
    };

    const containerVariants = {
        hidden: { opacity: 0, x: 20 },
        visible: {
            opacity: 1,
            x: 0,
            transition: {
                staggerChildren: 0.1,
                duration: 0.4
            }
        }
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 10 },
        visible: { opacity: 1, y: 0 }
    };

    return (
        <AppLayout>
            <Head title="Settings" />

            <div className="p-6 lg:p-10 max-w-[1200px] mx-auto">
                {/* Header */}
                <header className="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-10">
                    <div>
                        <div className="flex items-center gap-2 text-sage mb-2">
                            <SettingsIcon className="w-4 h-4" strokeWidth={1.5} />
                            <span className="text-label">System Configuration</span>
                        </div>
                        <h1 className="text-display-lg">Settings</h1>
                        <p className="text-caption mt-1">Configure your {usePage().props.settings?.app_name || 'Mapleby'} platform.</p>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="text-right hidden sm:block">
                            <p className="text-label">Status</p>
                            <div className="flex items-center gap-2 text-success font-medium text-sm">
                                <span className="w-2 h-2 rounded-full bg-success animate-pulse" />
                                All Systems Live
                            </div>
                        </div>
                        <Button
                            onClick={submit}
                            loading={processing}
                            className="btn-sage h-12 px-8"
                        >
                            <Save className="w-4 h-4" strokeWidth={2} />
                            Save Configuration
                        </Button>
                    </div>
                </header>

                <div className="grid lg:grid-cols-[280px_1fr] gap-8">
                    {/* Side Navigation */}
                    <aside>
                        <div className="card-serene p-4 sticky top-6">
                            <p className="text-label px-3 mb-4">Configuration</p>
                            <div className="space-y-1">
                                {categories.map((cat) => (
                                    <button
                                        key={cat.id}
                                        onClick={() => setActiveTab(cat.id)}
                                        className={cn(
                                            "w-full flex items-center gap-3 p-3 rounded-xl transition-all duration-200 group relative",
                                            activeTab === cat.id
                                                ? "bg-sage-mist text-sage"
                                                : "text-stone hover:bg-cream hover:text-charcoal"
                                        )}
                                    >
                                        <div className={cn(
                                            "w-9 h-9 rounded-lg flex items-center justify-center transition-all",
                                            activeTab === cat.id ? "bg-sage text-white" : "bg-cream text-drift"
                                        )}>
                                            <cat.icon className="w-4 h-4" strokeWidth={1.5} />
                                        </div>
                                        <div className="text-left">
                                            <p className="font-medium text-sm">{cat.label}</p>
                                            <p className="text-[11px] text-pebble">{cat.description}</p>
                                        </div>
                                        {activeTab === cat.id && (
                                            <ChevronRight className="ml-auto w-4 h-4 text-sage" />
                                        )}
                                    </button>
                                ))}
                            </div>

                            <div className="mt-6 p-4 bg-cream rounded-xl">
                                <p className="text-xs font-semibold text-charcoal mb-1">Pro Tip</p>
                                <p className="text-[11px] text-drift leading-relaxed">
                                    Use <kbd className="bg-white px-1.5 py-0.5 rounded text-charcoal text-[10px] font-mono border border-linen">Ctrl+S</kbd> to save quickly.
                                </p>
                            </div>
                        </div>
                    </aside>

                    {/* Main Content */}
                    <main>
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={activeTab}
                                initial="hidden"
                                animate="visible"
                                exit={{ opacity: 0, x: -20 }}
                                variants={containerVariants}
                                className="space-y-6"
                            >
                                {activeTab === 'general' && (
                                    <div className="space-y-6">
                                        <SectionHeader
                                            icon={Globe}
                                            title="General Settings"
                                            description="Core application settings and branding"
                                        />

                                        <Card className="p-8">
                                            <div className="grid md:grid-cols-2 gap-6">
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">App Name</Label>
                                                    <Input
                                                        value={data.settings.app_name || ''}
                                                        onChange={(e) => updateSetting('app_name', e.target.value)}
                                                        className="h-12"
                                                        placeholder="e.g. Mapleby Logistics"
                                                    />
                                                </motion.div>
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Support Email</Label>
                                                    <Input
                                                        type="email"
                                                        value={data.settings.support_email || ''}
                                                        onChange={(e) => updateSetting('support_email', e.target.value)}
                                                        className="h-12"
                                                        placeholder="support@example.com"
                                                    />
                                                </motion.div>
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Default Currency</Label>
                                                    <Input
                                                        value={data.settings.default_currency || ''}
                                                        onChange={(e) => updateSetting('default_currency', e.target.value)}
                                                        className="h-12 uppercase"
                                                        placeholder="USD"
                                                    />
                                                </motion.div>
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Timezone</Label>
                                                    <Select
                                                        value={data.settings.timezone || 'UTC'}
                                                        onValueChange={(val) => updateSetting('timezone', val)}
                                                    >
                                                        <SelectTrigger className="h-12">
                                                            <SelectValue placeholder="Select Timezone" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="UTC">UTC</SelectItem>
                                                            <SelectItem value="America/New_York">Eastern Time (US)</SelectItem>
                                                            <SelectItem value="America/Chicago">Central Time (US)</SelectItem>
                                                            <SelectItem value="America/Denver">Mountain Time (US)</SelectItem>
                                                            <SelectItem value="America/Los_Angeles">Pacific Time (US)</SelectItem>
                                                            <SelectItem value="Europe/London">London</SelectItem>
                                                            <SelectItem value="Europe/Paris">Paris</SelectItem>
                                                            <SelectItem value="Africa/Lagos">Lagos</SelectItem>
                                                            <SelectItem value="Asia/Dubai">Dubai</SelectItem>
                                                            <SelectItem value="Asia/Singapore">Singapore</SelectItem>
                                                            <SelectItem value="Asia/Tokyo">Tokyo</SelectItem>
                                                            <SelectItem value="Australia/Sydney">Sydney</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </motion.div>

                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Site Logo</Label>
                                                    <div className="flex items-center gap-4">
                                                        {settingsData.general?.site_logo && (
                                                            <div className="w-12 h-12 bg-cream rounded-lg flex items-center justify-center p-2 border border-linen">
                                                                <img src={`/storage/${settingsData.general.site_logo}`} alt="Logo" className="max-w-full max-h-full object-contain" />
                                                            </div>
                                                        )}
                                                        <Input
                                                            type="file"
                                                            onChange={(e) => setData('site_logo', e.target.files[0])}
                                                            className="h-12 pt-3 cursor-pointer"
                                                            accept="image/*"
                                                        />
                                                    </div>
                                                </motion.div>

                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Favicon</Label>
                                                    <div className="flex items-center gap-4">
                                                        {settingsData.general?.site_favicon && (
                                                            <div className="w-12 h-12 bg-cream rounded-lg flex items-center justify-center p-2 border border-linen">
                                                                <img src={`/storage/${settingsData.general.site_favicon}`} alt="Favicon" className="max-w-full max-h-full object-contain" />
                                                            </div>
                                                        )}
                                                        <Input
                                                            type="file"
                                                            onChange={(e) => setData('site_favicon', e.target.files[0])}
                                                            className="h-12 pt-3 cursor-pointer"
                                                            accept="image/*"
                                                        />
                                                    </div>
                                                </motion.div>
                                            </div>
                                        </Card>
                                    </div>
                                )}

                                {activeTab === 'email' && (
                                    <div className="space-y-6">
                                        <SectionHeader
                                            icon={Mail}
                                            title="Email Configuration"
                                            description="SMTP settings for transactional emails"
                                        />

                                        <Card className="p-8">
                                            <div className="space-y-8">
                                                <div className="grid md:grid-cols-3 gap-6">
                                                    <motion.div variants={itemVariants} className="space-y-2">
                                                        <Label className="text-charcoal font-medium">SMTP Host</Label>
                                                        <Input
                                                            value={data.settings.smtp_host || ''}
                                                            onChange={(e) => updateSetting('smtp_host', e.target.value)}
                                                            className="h-12"
                                                            placeholder="smtp.example.com"
                                                        />
                                                    </motion.div>
                                                    <motion.div variants={itemVariants} className="space-y-2">
                                                        <Label className="text-charcoal font-medium">Port</Label>
                                                        <Input
                                                            value={data.settings.smtp_port || ''}
                                                            onChange={(e) => updateSetting('smtp_port', e.target.value)}
                                                            className="h-12"
                                                            placeholder="587"
                                                        />
                                                    </motion.div>
                                                    <motion.div variants={itemVariants} className="space-y-2">
                                                        <Label className="text-charcoal font-medium">Encryption</Label>
                                                        <Input
                                                            value={data.settings.smtp_encryption || ''}
                                                            onChange={(e) => updateSetting('smtp_encryption', e.target.value)}
                                                            className="h-12"
                                                            placeholder="tls"
                                                        />
                                                    </motion.div>
                                                </div>

                                                <div className="grid md:grid-cols-2 gap-6">
                                                    <motion.div variants={itemVariants} className="space-y-2">
                                                        <Label className="text-charcoal font-medium">Username</Label>
                                                        <Input
                                                            value={data.settings.smtp_username || ''}
                                                            onChange={(e) => updateSetting('smtp_username', e.target.value)}
                                                            className="h-12"
                                                        />
                                                    </motion.div>
                                                    <motion.div variants={itemVariants} className="space-y-2">
                                                        <Label className="text-charcoal font-medium">Password</Label>
                                                        <Input
                                                            type="password"
                                                            value={data.settings.smtp_password || ''}
                                                            onChange={(e) => updateSetting('smtp_password', e.target.value)}
                                                            className="h-12"
                                                        />
                                                    </motion.div>
                                                </div>
                                            </div>

                                            <div className="mt-8 pt-8 border-t border-linen">
                                                <h3 className="text-base font-semibold text-charcoal mb-4 flex items-center gap-2">
                                                    <CheckCircle2 className="w-4 h-4 text-success" strokeWidth={2} />
                                                    Test Connection
                                                </h3>
                                                <div className="flex gap-4 items-end">
                                                    <div className="flex-1 space-y-2">
                                                        <Label className="text-charcoal font-medium">Test Email</Label>
                                                        <Input
                                                            type="email"
                                                            value={testEmail}
                                                            onChange={(e) => setTestEmail(e.target.value)}
                                                            className="h-12"
                                                            placeholder="Enter email to test"
                                                        />
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        onClick={handleTestSmtp}
                                                        disabled={testingSmtp || !testEmail}
                                                        variant="sage"
                                                        className="h-12 px-6"
                                                    >
                                                        {testingSmtp ? 'Sending...' : 'Send Test'}
                                                    </Button>
                                                </div>
                                            </div>
                                        </Card>
                                    </div>
                                )}

                                {activeTab === 'finance' && (
                                    <div className="space-y-6">
                                        <SectionHeader
                                            icon={CreditCard}
                                            title="Financial Settings"
                                            description="Tax rates and fee configuration"
                                        />

                                        <Card className="p-8">
                                            <div className="grid md:grid-cols-2 gap-8">
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">VAT Rate</Label>
                                                    <div className="relative">
                                                        <Input
                                                            type="number"
                                                            value={data.settings.vat_rate || 0}
                                                            onChange={(e) => updateSetting('vat_rate', e.target.value)}
                                                            className="h-12 pr-12 text-lg"
                                                        />
                                                        <span className="absolute right-4 top-1/2 -translate-y-1/2 text-drift font-semibold">%</span>
                                                    </div>
                                                </motion.div>
                                                <motion.div variants={itemVariants} className="space-y-2">
                                                    <Label className="text-charcoal font-medium">Handling Fee</Label>
                                                    <div className="relative">
                                                        <Input
                                                            type="number"
                                                            value={data.settings.handling_fee || 0}
                                                            onChange={(e) => updateSetting('handling_fee', e.target.value)}
                                                            className="h-12 pl-10 text-lg"
                                                        />
                                                        <span className="absolute left-4 top-1/2 -translate-y-1/2 text-drift font-semibold">$</span>
                                                    </div>
                                                </motion.div>
                                            </div>
                                        </Card>
                                    </div>
                                )}

                                {activeTab === 'security' && (
                                    <div className="space-y-6">
                                        <SectionHeader
                                            icon={Shield}
                                            title="Security Settings"
                                            description="Access controls and protection"
                                        />

                                        <Card className="p-8">
                                            <div className="space-y-6">
                                                <div className="p-5 bg-sage-mist border border-sage-soft rounded-xl flex items-center gap-5">
                                                    <div className="w-11 h-11 rounded-xl bg-sage flex items-center justify-center">
                                                        <Shield className="w-5 h-5 text-white" strokeWidth={1.5} />
                                                    </div>
                                                    <div className="flex-1">
                                                        <p className="text-charcoal font-semibold">Two-Factor Authentication</p>
                                                        <p className="text-sm text-drift">Add extra security to admin accounts</p>
                                                    </div>
                                                    <Button variant="outline" className="h-10">Configure</Button>
                                                </div>

                                                <div className="space-y-4">
                                                    <div className="flex items-center justify-between p-5 rounded-xl bg-cream border border-linen">
                                                        <div>
                                                            <p className="text-charcoal font-medium">Strict Password Policy</p>
                                                            <p className="text-sm text-drift">Require special characters and 12+ characters</p>
                                                        </div>
                                                        <div
                                                            className={cn(
                                                                "w-12 h-7 rounded-full p-1 flex cursor-pointer transition-colors duration-200",
                                                                data.settings.strict_password_policy ? "bg-sage justify-end" : "bg-sand justify-start"
                                                            )}
                                                            onClick={() => updateSetting('strict_password_policy', !data.settings.strict_password_policy)}
                                                        >
                                                            <motion.div layout className="w-5 h-5 rounded-full bg-white shadow-sm" />
                                                        </div>
                                                    </div>

                                                    <div className="flex items-center justify-between p-5 rounded-xl bg-cream border border-linen">
                                                        <div>
                                                            <p className="text-charcoal font-medium">Session Timeout</p>
                                                            <p className="text-sm text-drift">Auto-logout after 60 minutes of inactivity</p>
                                                        </div>
                                                        <div
                                                            className={cn(
                                                                "w-12 h-7 rounded-full p-1 flex cursor-pointer transition-colors duration-200",
                                                                data.settings.session_timeout ? "bg-sage justify-end" : "bg-sand justify-start"
                                                            )}
                                                            onClick={() => updateSetting('session_timeout', !data.settings.session_timeout)}
                                                        >
                                                            <motion.div layout className="w-5 h-5 rounded-full bg-white shadow-sm" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </Card>
                                    </div>
                                )}
                            </motion.div>
                        </AnimatePresence>
                    </main>
                </div>
            </div>
        </AppLayout>
    );
}

function SectionHeader({ icon: Icon, title, description }) {
    return (
        <div className="flex items-center gap-4 mb-2">
            <div className="w-12 h-12 rounded-xl bg-sage-mist flex items-center justify-center text-sage border border-sage-soft">
                <Icon className="w-5 h-5" strokeWidth={1.5} />
            </div>
            <div>
                <h2 className="text-display-md">{title}</h2>
                <p className="text-caption">{description}</p>
            </div>
        </div>
    );
}
