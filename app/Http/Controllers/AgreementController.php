<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Packages\AgreementService\Services\AgreementService;
use App\Models\Agreement;

class AgreementController extends Controller
{
    public function index()
    {
        $agreements = Agreement::all();
        return view('agreement.index', ['agreements' => $agreements]);
    }

    public function store(Request $request, AgreementService $agreementService)
    {
        $data = $request->validate([
            'customer_id' => 'required|string',
            'strategy' => 'required|string',
            'multiplier' => 'required|numeric',
            'vat_rate' => 'required|numeric',
            'currency' => 'required|string',
            'locale' => 'required|string',
            'rules' => 'required|json',
        ]);

        $customerId = $data['customer_id'];
        unset($data['customer_id']);
        $data['rules'] = json_decode($data['rules'], true);

        $agreementService->createNewVersion($customerId, $data);

        return back()->with('success', 'Agreement created successfully!');
    }

    public function destroy(Agreement $agreement)
    {
        $agreement->delete();
        return back()->with('success', 'Agreement deleted successfully!');
    }
}