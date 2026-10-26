<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\CRM\Entities\Lead;
use Modules\CRM\Entities\Client;
use App\Models\User;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->input('view', 'list'); // kanban or list

        $query = Lead::with(['client', 'assignee']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        if ($view === 'list') {
            $leads = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        } else {
            // For Kanban, we usually load all open leads
            $leads = $query->whereNotIn('status', ['lost', 'won']) // Optional: Hide closed in Kanban by default? Or show all.
                ->get()
                ->groupBy('status');
        }

        // Get dropdown data
        $clients = Client::orderBy('company_name')->select('id', 'company_name', 'contact_person')->get();
        $users = User::select('id', 'name')->get();

        // Calculate Stats for Insights (Status Distribution)
        // Group by status
        $stats = Lead::selectRaw('status, count(*) as value')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst($item->status),
                    'value' => $item->value
                ];
            });

        return Inertia::render('CRM/Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'view']),
            'clients' => $clients,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'status' => 'required|string',
            'estimated_value' => 'nullable|numeric',
            'source' => 'nullable|string',
            'priority' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Lead::create($validated);

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'status' => 'required|string',
            'estimated_value' => 'nullable|numeric',
            'source' => 'nullable|string',
            'priority' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->back()->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->back()->with('success', 'Lead deleted successfully.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|string']);
        $lead->update(['status' => $request->status]);
        return redirect()->back(); // No flash needed for drag-drop
    }
}
